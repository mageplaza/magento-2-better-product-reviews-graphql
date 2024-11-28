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
=======
# Magento 2 Product Reviews GraphQL

**Magento 2 Better Product Reviews GraphQL is now a part of Mageplaza Better Product Reviews extension that adds GraphQL features.** This is helpful for PWA compatibility. The module provides type and resolver information for the GraphQl module to generate catalog reviews information endpoints.

[Mageplaza Better Product Reviews for Magento 2](https://www.mageplaza.com/magento-2-better-product-reviews/) is an effective solution for online stores to showcase product reviews. 

The extension enables online stores to display reviews not only because it’ll help promote the products with good reviews but also to provide customers or first-time visitors with useful information about the products. The previous customers’ reviews are great sources to help customers understand the products better to make the right purchasing decisions. 

Each product will have an overall assessment which shows the number of reviews, the average rate, recommendation percentage, and star ratings. Customers can quickly compare products via their overall assessment, therefore come into the purchasing decision more quickly. 

Showing the reviews is great but it'll be even better if the reviews are authentic and the one who writes the reviews is trustworthy. Magento 2 Better Product Reviews enables showing the verified purchase badge to prove that the reviews are totally written by a real purchaser. Customers can also add images to illustrate what they say more vividly. The visual review combined with a verified reviewer will be a more persuasive incentive for customers to spend money on your product. 

The extension also enables you to display product reviews on the search engine results. These SEO-friendly reviews draw the customers’ attention to your page and click to view more details of products with good reviews. 

The extension also enables you to display a review reminder right on the customer account page to remind those who haven’t left the reviews yet. This review reminder slide will appear after the list of products waiting for the reviews. Customers can add reviews quickly with a simple click on the “Write a review” button. 

