# LabelEpisode WordPress

LabelEpisode WordPress project

## General Guidance

Only make code changes in themes/depot-child.

If not possible, check in plugins/some-plugin, commit, make your fix in plugins/some-plugin, commit again, so that the second commit contains only minimal patch code.

Also, to ease search through Github commits, add text "**#source-patch**"" in commit message.


## Deployment

Assign a tag **server-head** to the current commit where server has been updated to.

At development phase, we will reuse and move the same tag to HEAD everytime server has been deployed. Since Git tag can only be assigned to one commit, we have to do the following:

```
git tag server-head some-commit
git tag -d server-head
git tag server-head new-commit-server-deployed
git push --force origin server-head
```

Use labelepisode cli `le` to deploy as below:

  - incremental, sftp only diffs after a commit/tag, (mostly, server-head)
    ```
    le deploy -h labelepisode.com -u z9dxje3vnh6c -r /home/z9dxje3vnh6c/public_html -c server-head
    ```
  - full, sftp all local tracked files


## Plugin Versions

List of plugin versions (Apr, 2019)

```wp plugin list```

| name                                        | status   | update    | version |
| ------------------------------------------- | -------- | --------- | ------- |
| akismet                                     | active   | none      | 4.1.1   |
| taxonomy-terms-order                        | active   | none      | 1.5.5   |
| classic-editor                              | active   | none      | 1.4     |
| duplicator                                  | active   | none      | 1.3.10  |
| first-order-discount-woocommerce            | active   | none      | 1.8     |
| jetpack                                     | active   | none      | 7.2.1   |
| mailchimp-for-woocommerce                   | active   | none      | 2.1.15  |
| mikado-core                                 | active   | none      | 1.1     |
| revslider                                   | active   | none      | 5.4.8   |
| taxjar-simplified-taxes-for-woocommerce     | active   | none      | 2.1.0   |
| woocommerce                                 | active   | none      | 3.5.7   |
| woo-variation-gallery                       | active   | available | 1.1.23  |
| wc-frontend-manager                         | active   | available | 6.0.0   |
| wc-multivendor-marketplace                  | active   | available | 3.0.0   |
| wc-multivendor-membership                   | active   | available | 2.4.2   |
| woocommerce-gateway-paypal-express-checkout | active   | none      | 1.6.10  |
| woocommerce-services                        | active   | none      | 1.19.0  |
| woocommerce-square                          | inactive | none      | 1.0.35  |
| woocommerce-gateway-stripe                  | active   | none      | 4.1.15  |
| js_composer                                 | active   | available | 5.5.2   |
| wp-mail-logging                             | active   | available | 1.8.5   |
| wp-super-cache                              | active   | none      | 1.6.4   |
| yith-advanced-refund-system-for-woocommerce | active   | none      | 1.0.8   |
| advanced-cache.php                          | dropin   | none      |         |


## Manual Changes

Remove mc-multivendor-marketplace add product popup
```
/store-manager/setting -> Modules -> turn off "Popup Add Product"
```
