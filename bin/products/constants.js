const MEN_CATEGORY_ID = 40;
const WOMEN_CATEGORY_ID = 41;

module.exports = {
    // IDs need special handling
    SPECIAL_ID: [
        // Earing, we need remove 'Size' variation, and allow '数量' (single or pair style)
        460, 461, 462, 463, 464, 465, 510, 511, 512,
    ],
    SKIP_ID: [
        // 胸针, simple item
        468
    ],
    GROUP_TO_CATEGORY: {
        1: MEN_CATEGORY_ID,
        2: WOMEN_CATEGORY_ID,
        3: [MEN_CATEGORY_ID, WOMEN_CATEGORY_ID],
    },
    GROUPS: {
        1: ['m'], // Men
        2: ['w'], // Women
        3: ['m', 'w'], // All
    },
    CATEGORIES: {
        1: {w: 46, m: 43}, // 衣服 - Clothing
        2: {w: 173, m: 18}, // ├ T恤/POLO - T-Shirts/POLO
        5: {w: 174, m: 17}, // ├ 卫衣 - Hoodies
        25: {w: 249, m: 248}, // ├ 针织衫 - Knitwear
        13: {w: 175, m: 190}, // ├ 衬衫 - Shirts
        43: {w: 176, m: 191}, // └ 毛衣 - Sweaters

        6: {w: 153, m: 192}, // 裤装 - Bottoms
        15: {w: 177},         // ├ 短裙 - Skirts
        16: {w: 178, m: 193}, // ├ 短裤 - Shorts
        28: {w: 179, m: 194}, // ├ 阔腿裤 - Wide-leg Trousers
        29: {w: 180, m: 195}, // ├ 运动裤 - Sweatpants
        30: {w: 181, m: 196}, // ├ 正装裤 - Suit Pants
        45: {w: 182, m: 197}, // ├ 休闲裤 - Casuals
        46: {w: 183},         // └ 裙裤 - Pantskirts

        37: {w: 154, m: 198}, // 外装 - Outwear
        40: {w: 184, m: 15}, // ├ 大衣 - Coats
        38: {w: 185, m: 200}, // ├ 夹克 - Jackets
        39: {w: 186, m: 201}, // ├ 休闲西装 - Blazers
        41: {w: 187, m: 202}, // ├ 羽绒服 - Down Jackets
        47: {w: 188, m: 203}, // └ 正装西装 - Suits

        32: {w: 155},         // 裙装 - Dresses
        33: {w: 164},         // ├ 连衣裙 - Overalls
        35: {w: 164},         // ├ 长裙 - Maxi & Midi Dresses
        36: {w: 165},         // ├ 短裙 - Mini Dresses
        44: {w: 166},         // ├ 吊带裙 - Shoulder-strap Skirts
        60: {w: 167},         // └ 礼裙 - Evening/Party Dresses

        7: {w: 144},         // 箱包 - Handbags
        17: {w: 145, m: 217}, // ├ 双肩包 - Backpacks
        48: {w: 146, m: 218}, // ├ 手提包 - Totes
        49: {w: 147, m: 219}, // ├ 钱包  - Wallets
        50: {w: 148, m: 220}, // ├ 单肩包 - Shoulder Bags
        51: {w: 149, m: 221}, // ├ 卡包 - Card Cases
        52: {w: 150},         // ├ 手包 - Clutches
        53: {},                 // ├ 电脑包 - Laptop Bags
        54: {w: 151, m: 222}, // └ 腰包 - Belt Bags

        8: {w: 48, m: 16},   // 首饰  Jewelry & Accessories
        19: {w: 168, m: 211}, // ├ 项链  Necklaces
        18: {w: 169, m: 213}, // ├ 耳环  Earrings
        23: {w: 170, m: 212}, // ├ 手链  Bracelets
        24: {w: 171, m: 214}, // ├ 戒指  Rings
        34: {w: 172, m: 215}, // └ 别针  Brooches
        63: {},                 // 脚链

        9: {w: 47, m: 204},  // 鞋 Shoes
        20: {w: 156, m: 205}, // ├ 平底鞋 Flats
        21: {w: 157},         // ├ 高跟鞋 High-heels
        42: {w: 158, m: 206}, // ├ 运动鞋 Sneakers
        55: {w: 159},         // ├ 短靴  Booties
        56: {w: 160, m: 207}, // ├ 长靴  Boots
        57: {w: 161, m: 209}, // ├ 拖鞋  Slippers
        61: {w: 162},         // ├ 中跟鞋 Mid-heels
        62: {w: 163, m: 210}, // └ 凉拖  Sandals

        58: {},                 // 生活精选
        59: {},                 // 手机壳
        64: {},                 // 袖扣
    },
    CATEGORY_MEN: MEN_CATEGORY_ID,
    CATEGORY_WOMEN: WOMEN_CATEGORY_ID,
    VARIATION: {
        '尺码': 'Size',
        '颜色': 'Color',
        '码数': 'Size',
        '数量': 'Style', // earing single/pair
    },
    // Maps wechat brands to wordpress brand.
    BRANDS: {
        3: 223, // Blancore
        7: 224, // FIFTH AVE
        12: 225, // GEMHOLIC
        1: 227, // Label Episode
        19: 228, // MARSEVEN
        5: 229, // Nashascope 买手店
        10: 230, // NONE OF MY BUSINESS
        8: 231, // Slumber Chi 买手店
        11: 232, // TIMFORMATION
        4: 233, // UNDERGRADUATE
        20: 234, // XINYEJIANG
        9: 235, // YEE SI
        24: 236, // The Dirty Collection
        22: 237, // LECRESCENDO
        21: 238, // XIAOXU
        2: 239, // IIMAGEPLUS
        'MS MIN': 240,
        'YIRANTIAN': 241,
        'ACLER': 242,
        'NOMANOMAN': 243,
        'JINNNN': 244,
        'YUUL YIE': 245,
        'VVNK': 246,
        'Beton Cire': 247
    },
    BRAND_NASHA_ID: "5",
    BRAND_NASHA: 'Nashascope 买手店',
    // eu (cn) -> us
    // wconcept shoe size chart
    W_SHOE_SIZE: {
        '35': '5',
        '35.5': '5.5',
        '36': '6',
        '36.5': '6.5',
        '37': '7',
        '37.5': '7.5',
        '38': '8',
        '38.5': '8.5',
        '39': '9',
    },
    M_SHOE_SIZE: {
        '40': '6.5',
        '40.5': '7',
        '41': '7.5',
        '41.5': '8',
        '42': '8.5',
        '42.5': '9',
        '43': '9.5',
        '43.5': '10',
        '43': '10.5',
    },
    formatSku: function (product_ware_id) {
        return `px${product_ware_id}`;
    }
}
