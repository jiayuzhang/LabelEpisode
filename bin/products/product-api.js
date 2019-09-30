const _ = require('lodash');

const WooCommerceAPI = require('woocommerce-api');
const api = new WooCommerceAPI({
    url: 'http://127.0.0.1/dup',
    consumerKey: 'ck_9ac54c237b32e3d85e8697e99ce8e6dc4122ccbd',
    consumerSecret: 'cs_32cd477546becc2f2fd55810060d72e922f749b1',
    // url: 'https://www.labelepisode.com/home',
    // consumerKey: 'ck_c23f387bff044f634ae63a8b193c5f0fa195cc6c',
    // consumerSecret: 'cs_1b6459782536ac3a3cda05970ffb13c6124a8f2d',
    wpAPI: true,
    version: 'wc/v3',
    // Force Basic Authentication as query string true and using under HTTPS
    // GoDaddy apache has issue to passing AUTH header...
    // queryStringAuth: true,
    timeout: 1000000000
});

module.exports = {
    create: async function (product) {
        const response = await api.postAsync('products', product);
        const createProduct = JSON.parse(response.body);
        if (!hasOkayStatus(response.statusCode)) {
            throw `Error creating product ${product}: ${createProduct.message}`;
        }
        return createProduct;
    },
    createVariation: async function (productId, variations) {
        const data = {create: variations};
        // TODO: For earing, remove '尺码'/Size attributes
        const response = await api.postAsync(`products/${productId}/variations/batch`, data);
        const createdVariations = JSON.parse(response.body);
        if (!hasOkayStatus(response.statusCode)) {
            throw `Error creating variations ${variations}: ${createdVariations.message}`;
        }
        return createdVariations;
    },
    update: function (product) {
        //TODO: implement update.
        return null;
    },
    getBySku: async function (sku) {
        const response = await api.getAsync(`products?sku=${sku}`);
        const product = JSON.parse(response.body);
        // If response contains error code.
        if (!hasOkayStatus(response.statusCode)) {
            throw `Error fetch product by sku: ${sku}: ${product.message}`;
        }
        return product;
    },
    getBySlug: async function (slug) {
        const response = await api.getAsync(`products?slug=${slug}`);
        const product = JSON.parse(response.body);
        if (!hasOkayStatus(response.statusCode)) {
            throw `Error fetch product by slug: ${slug}`;
        }
        return product;
    }
};

function hasOkayStatus(statusCode) {
    return 200 <= statusCode && statusCode < 300;
}