# Better Product Reviews GraphQl
This module provides type and resolver information for the GraphQl module to generate catalog reviews information endpoints.

## 1. How to install
Run the following command in Magento 2 root folder:

```
composer require mageplaza/module-better-product-reviews-graphql
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## 2. How to use

To start working with GraphQl in Magento, you need the following:
- Use Magento 2.3.x. Returns site to developer mode
- Install [chrome extension](https://chrome.google.com/webstore/detail/chromeiql/fkkiamalmpiidkljmicmjfbieiclmeij?hl=en) (currently does not support other browsers)
- Set **GraphQL endpoint** as `http://<magento2-3-server>/graphql` in url box, click **Set endpoint**. (e.g. http://develop.mageplaza.com/graphql/ce232/graphql)
- Mageplaza-supported queries are fully written in the **Description** section of `Query.productreviews.Products`

![](https://i.imgur.com/8OW0Y2G.png)

## 3. Devdocs
- [Better Product Reviews API & examples](https://documenter.getpostman.com/view/5977924/SWE29gRM?version=latest)
- [Better Product Reviews GraphQL & examples](https://documenter.getpostman.com/view/10589000/TVYGcdFp)
