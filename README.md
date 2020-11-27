# Magento 2 Product Reviews GraphQL / PWA

[Mageplaza Better Product Reviews for Magento 2](https://www.mageplaza.com/magento-2-better-product-reviews/) is an effective solution for online stores to showcase product reviews. 

The extension enables online stores to display reviews not only because it’ll help promote the products with good reviews but also to provide customers or first-time visitors with useful information about the products. The previous customers’ reviews are great sources to help customers understand the products better to make the right purchasing decisions. 

Each product will have an overall assessment which shows the number of reviews, the average rate, recommendation percentage, and star ratings. Customers can quickly compare products via their overall assessment, therefore come into the purchasing decision more quickly. 

Showing the reviews is great but it'll be even better if the reviews are authentic and the one who writes the reviews is trustworthy. Magento 2 Better Product Reviews enables showing the verified purchase badge to prove that the reviews are totally written by a real purchaser. Customers can also add images to illustrate what they say more vividly. The visual review combined with a verified reviewer will be a more persuasive incentive for customers to spend money on your product. 

The extension also enables you to display product reviews on the search engine results. These SEO-friendly reviews draw the customers’ attention to your page and click to view more details of products with good reviews. 

The extension also enables you to display a review reminder right on the customer account page to remind those who haven’t left the reviews yet. This review reminder slide will appear after the list of products waiting for the reviews. Customers can add reviews quickly with a simple click on the “Write a review” button. 

What’s more, **Magento 2 Better Product Reviews GraphQL is now a part of Mageplaza Better Product Reviews extension that adds GraphQL features.** This is helpful for PWA compatibility. The module provides type and resolver information for the GraphQl module to generate catalog reviews information endpoints.

## 1. How to install
Run the following command in Magento 2 root folder:

```
composer require mageplaza/module-better-product-reviews-graphql
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
**Note:**
Magento 2 Better Product Reviews GraphQL requires installing [Mageplaza Better Product Reviews](https://www.mageplaza.com/magento-2-better-product-reviews/) in your Magento installation. 

## 2. How to use

To start working with **Better Product Reviews GraphQl** in Magento, you need to:

- Use Magento 2.3.x. Return your site to developer mode.
- Install [chrome extension](https://chrome.google.com/webstore/detail/chromeiql/fkkiamalmpiidkljmicmjfbieiclmeij?hl=en) (currently does not support other browsers)
- Set **GraphQL endpoint** as `http://<magento2-3-server>/graphql` in url box, click **Set endpoint**. (e.g. http://develop.mageplaza.com/graphql/ce232/graphql)
- Perform a query in the left cell then click the **Run** button or **Ctrl + Enter** to see the result in the right cell.
- To see the supported queries for **Better Product Reviews GraphQl** of Mageplaza, you can look in `Docs > Query > mpBprGetReview` in the right corner.

![](https://i.imgur.com/cygqEwo.png)

- In addition, you can create reviews from products and to see if mutations are supported to create reviews from **Mageplaza's Better Product Reviews GraphQl**, you can look at the top right corner of `Docs > Mutation > Create a new Review`.

![](https://i.imgur.com/zgmHrt1.png)

## 3. Devdocs
- [Magento 2 Better Product Reviews API & examples](https://documenter.getpostman.com/view/5977924/SWE29gRM?version=latest)
- [Magento 2 Better Product Reviews GraphQL & examples](https://documenter.getpostman.com/view/10589000/TVYGcdFp)

## 4. Contribute to this module
Feel free to **Fork** and contribute to this module. 
You can create a pull request, and we will consider to merge your changes in the main branch. 

## 5. Get support
- Don't hesitate to [contact us](https://www.mageplaza.com/contact.html) if you have any questions or additional ideas for this post. Our support team is always here to help. 
- If you find it helpful, please give us a **Star** ![star](https://i.imgur.com/S8e0ctO.png)

