#!/usr/bin/env node
const _ = require('lodash');
const wxApi = require('./wx-api');
const constants = require('./constants.js');
const converter = require('./product-converter.js');
const woocommerceProductApi = require('./product-api.js');
const minimist = require('minimist');
const process = require('process');
const winston = require('winston');

const logger = winston.createLogger({
    level: 'info',
    format: winston.format.combine(winston.format.json(), winston.format.timestamp()),
    transports: [
        //
        // - Write to all logs with level `info` and below to `combined.log`
        // - Write all logs error (and below) to `error.log`.
        //
        new winston.transports.File({filename: 'error.log', level: 'error'}),
        new winston.transports.File({filename: 'combined.log'}),
        new winston.transports.Console(),
    ]
});

let dryRun = true;

async function run() {
    let pageNumber = 1;
    const pageSize = 100;
    const startTime = _.now();
    let totalProductsCount = 0;
    let successCount = 0;
    const failedProducts = {};
    logger.info(`Starting sync at ${startTime}, with pageSize[${pageSize}]`);
    while (true) {
        logger.info(`Fetching products from wx-api page number: ${pageNumber}`);
        const products = await wxApi.get_products(pageNumber, pageSize);

        for (const product_json of products) {
            logger.info(`Working on product ${JSON.stringify(product_json)}`);
            const converted = converter.convertProduct(product_json);
            if (_.isEmpty(converted.error)) {
                logger.info(`Converted product: ${JSON.stringify(converted)}`);
            } else {
                let errorMsg = JSON.stringify(converted.error);
                logger.error(`Conversion errors: ${errorMsg}`);
                failedProducts[constants.formatSku(product_json.ware_id)] = converted.error;
                continue;
            }

            if (dryRun) {
                logger.info(`skip saving product ${converted.sku} since in dryRun mode`);
                continue;
            }
            try {
                await save_product(product_json, converted);
                successCount++;
            } catch (err) {
                let errorMsg = `Error saving product (${converted.sku}: ${err}`;
                logger.error(errorMsg);
                failedProducts[converted.sku] = err;
                 continue;
            }
        }

        totalProductsCount += products.length;
        if (products.length < pageSize) {
            break;
        } else {
            pageNumber++;
        }
        await sleep(5000);
    }
    const endTime = _.now();
    logger.info(`Finished sync at ${endTime}, elapsed time ${endTime - startTime}`);
    logger.info(`Synced ${totalProductsCount} products`);
    logger.info(`Succeed ${successCount} products`);
    logger.info(`Failed on ${_.size(failedProducts)} products`);
    // logger.info(`Failed products: ${JSON.stringify(failedProducts, null, 2)}`);
}

async function save_product(product_json, product) {
    const existingProduct = await woocommerceProductApi.getBySku(product.sku);
    if (_.isEmpty(existingProduct)) {
        add_metadata(product_json, product);
        logger.info(`Creating new product for sku ${product.sku}`);
        if (!dryRun) {
            const createdProduct = await woocommerceProductApi.create(product);
            logger.info(`Successfully created product ${JSON.stringify(createdProduct)}`);

            logger.info(`Creating variations for sku ${product.sku}`);
            const variations = await woocommerceProductApi.createVariation(createdProduct.id,
                product.variations);
            logger.info(`Successfully created variations ${JSON.stringify(variations)}`);
        }
    } else if (shouldUpdateProduct(existingProduct, product)) {
        logger.info(`Updating product with sku ${product.sku}`);
        await woocommerceProductApi.update(product);
    } else {
        logger.info(`skip saving product sku: ${product.sku}`);
    }
}

function sleep(ms) {
    return new Promise(resolve => {
        setTimeout(resolve, ms);
    });
}

function shouldUpdateProduct(existingProduct, product) {
    // TODO: when should we update?
    return false;
}

/**
 * Add relevant meta_data
 * 1. last_sync_time
 * 2. the entire wx product json
 * 3. the entire original converted product.
 */
function add_metadata(product_json, product) {
    let meta_data = [{
        key: 'last_sync_time',
        value: product_json.update_at,
    }, {
        key: 'wx_product',
        value: product_json
    }, {
        key: 'original_product',
        value: _.cloneDeep(product)
    }];
    if (!_.isEmpty(product.meta_data)) {
        meta_data = _.union(product.meta_data, meta_data);
    }
    product.meta_data = meta_data;

    return product;
}

const catchFn = (err) => {
    logger.error('ERROR!', err);
    process.exit(0);
};

// Main program
const args = minimist(process.argv.slice(2));

const cmd = args._[0] || 'help';

switch (cmd) {
    case 'run':
        dryRun = false;
        run().catch(catchFn);
        break;

    case 'dry-run':
        dryRun = true;
        run().catch(catchFn);
        break;

    default:
        logger.error(`"${cmd}" is not a valid command!`);
}