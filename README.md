# Better Product Reviews GraphQl
This module provides type and resolver information for the GraphQl module to generate catalog reviews information endpoints.

## How to install
Run the following command in Magento 2 root folder:

```
composer require mageplaza/module-better-product-reviews-graphql
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## How to use

To start working with **Better Product Reviews GraphQl** in Magento, you need to:

- Use Magento 2.3.x. Return your site to developer mode
- Install [chrome extension](https://chrome.google.com/webstore/detail/chromeiql/fkkiamalmpiidkljmicmjfbieiclmeij?hl=en) (currently does not support other browsers)
- Set **GraphQL endpoint** as `http://<magento2-3-server>/graphql` in url box, click **Set endpoint**. (e.g. http://develop.mageplaza.com/graphql/ce232/graphql)
- Perform a query in the left cell then click the **Run** button or **Ctrl + Enter** to see the result in the right cell
- To see the supported queries for **Better Product Reviews GraphQl** of Mageplaza, you can look in `Docs > Query > mpBprGetReview` in the right corner

![](https://i.imgur.com/cygqEwo.png)

- In addition, you can create reviews from products and to see if mutations are supported to create reviews from **Mageplaza's Better Product Reviews GraphQl**, you can look at the top right corner of `Docs > Mutation > Create a new Review`.

![](https://i.imgur.com/zgmHrt1.png)

