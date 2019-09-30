const _ = require('lodash');
const constants = require('./constants.js');

module.exports = {
    convertProduct: function (product_json) {
        const errors = validateProduct(product_json);
        if (!_.isEmpty(errors)) {
            return {
                'error': {
                    product_id: product_json.ware_id,
                    msg: errors
                }
            };
        }
        return toProduct(product_json);
    }
};

/** Converts a product json to a product object. */
function toProduct(product_json) {
    const product = {};
    const sku = constants.formatSku(product_json.ware_id);
    product.status = 'draft';
    // Assume we only have variable product.
    product.type = 'variable';

    // GROUP is the top level category: Men, Women or both (array).
    const topCategory = constants.GROUP_TO_CATEGORY[product_json.group_id];
    const categories = [topCategory];
    categories.push(getCategoriesForGroup(product_json.group_id, product_json.category_id));
    if (!_.isEmpty(product_json.category_parent_id)) {
        categories.push(getCategoriesForGroup(
            product_json.group_id, product_json.category_parent_id));
    }
    product.categories = _.flatten(categories).map(id => {
        return {'id': id}
    });

    product.regular_price = convertPrice(product_json.market_price);
    product.sale_price = convertPrice(product_json.price);

    let images = [{src: product_json.picture}];
    if (!_.isEmpty(product_json.banner_list)) {
        images = _.union(images, _.map(product_json.banner_list, (pic) => {
            return {src: pic.picture};
        }));
    }
    product.images = images;

    const variations = product_json.skuList.map(variation => {
        return {
            regular_price: product.regular_price,
            sale_price: convertPrice(variation.price),
            sku: sku,
            attributes: variation.sku_info.map(attr => {
                return {
                    name: constants.VARIATION[attr.sku_key],
                    option: attr.sku_key === '码数'
                        ? convertShoeSize(attr.sku_value, topCategory)
                        : _.toUpper(attr.sku_value)
                }
            })
        };
    });
    product.variations = _.cloneDeep(variations);

    const attributes = _.reduce(_.uniq(_.flatMap(variations, (v => v.attributes))),
        function (result, attr) {
            if (result[attr.name]) {
                result[attr.name].options.push(attr.option);
            } else {
                result[attr.name] = {
                    name: attr.name,
                    visible: true,
                    variation: true,
                    options: [attr.option],
                };
            }
            return result;
        }, {});
    product.attributes = _.values(attributes);

    if (product_json.shop_id === constants.BRAND_NASHA_ID) {
        product.brand = constants.BRANDS[product_json.title];
        product.name = product_json.sub_title;
    } else {
        product.brand = constants.BRANDS[product_json.shop_id];
        product.name = product_json.title;
        product.short_description = product_json.sub_title;
    }
    product.description = product_json.notes;
    product.sku = sku;

    return product;
}

function getCategoriesForGroup(group_id, category_id) {
    return _.flatMap(constants.GROUPS[group_id],
        (group) => constants.CATEGORIES[category_id][group]);
}

function validateProduct(product_json) {
    const errors = [];
    // 1. title and subTitle not null
    if (_.isEmpty(product_json.title) || _.isEmpty(product_json.sub_title)) {
        errors.push('title and subTitle not null');
    }
    // 2. At least, one pic
    if (_.isEmpty(product_json.picture)) {
        errors.push('at least one pic');
    }
    // 3. Must have category mapping
    const topCategory = constants.GROUP_TO_CATEGORY[product_json.group_id];
    if (!constants.CATEGORIES[product_json.category_id]
        || (!_.isEmpty(product_json.category_parent_id)
            && !constants.CATEGORIES[product_json.category_parent_id])) {
        errors.push('must have category mapping');
    }
    if (topCategory === constants.CATEGORY_MEN) {
        if (!constants.CATEGORIES[product_json.category_id]['m']) {
            errors.push('must have category mapping');
        }
    } else if (topCategory === constants.CATEGORY_WOMEN) {
        if (!constants.CATEGORIES[product_json.category_id]['w']) {
            errors.push('must have category mapping');
        }
    }
    // 4. Must have variation mapping
    if (_.isEmpty(product_json.skuList)) {
        errors.push('must have variation mapping');
    }
    const hasUnknownVariation = product_json.skuList.map(
        v => v.sku_info.map(attr => !!constants.VARIATION[attr.sku_key]));
    if (hasUnknownVariation.includes(false)) {
        errors.push('must have variation mapping');
    }
    // 5. Must have brand mapping
    if (product_json.shop_id === constants.BRAND_NASHA_ID) {
        if (!constants.BRANDS[product_json.title]) {
            errors.push('must have brand mapping');
        }
    } else if (!constants.BRANDS[product_json.shop_id]) {
        errors.push('must have brand mapping');
    }

    // if (product_json.category_id == 33 || product_json.category_id == 25) {
    //     errors.push(`The product ${product_json.ware_id} category ${product_json.category_id}
    // might require review.`); }

    return errors;
}

function convertShoeSize(shoeSizeStr, topCategory) {
    if (shoeSizeStr.startsWith('#')) {
        shoeSizeStr = shoeSizeStr.substring(1);
    }

    const shoeSize = topCategory == constants.CATEGORY_MEN
        ? constants.M_SHOE_SIZE
        : constants.W_SHOE_SIZE;
    if (!shoeSize[shoeSizeStr]) {
        console.log(`Not found shoe size ${shoeSizeStr}`);
        // Return it as it is, we manually fix later
        return shoeSizeStr;
    }
    return shoeSize[shoeSizeStr];
}

function convertPrice(priceStr) {
    if (!priceStr) {
        return '';
    }
    // RMB to USD, by 9/26/19 currency exchange rate
    const usd = Math.ceil(_.toNumber(priceStr) * 0.14);
    return `${usd}.00`;
}