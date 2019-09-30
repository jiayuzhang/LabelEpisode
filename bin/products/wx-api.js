const rp = require('request-promise-native');

const product_list_url = 'https://wx.pin-xu.com/api/external/get_commodity_list';
const category_list_url = 'https://wx.pin-xu.com/api/external/get_category_list';
const shop_list_url = 'https://wx.pin-xu.com/api/external/get_shop_list';

module.exports = {
    get_products: async function(page_number, page_size) {
        const get_product_request = {
            url: product_list_url,
            method: 'POST',
            body: {
                page: page_number,
                page_size: page_size,
            },
            json: true,
        };
        const product_response = await rp(get_product_request);
        if (product_response.error_code !== 'SUCCESS') {
            throw `Failed to fetch products with request: ${request}`;
        }
        return product_response.result.lists;
    }
};
