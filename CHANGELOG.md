# Changelog

## 2.1.0

- Added 'Bundle' functionality - adding discounts based on customers purchasing a set of products
- Added `Bundle\Bundle` class, representing a bundle configuration as set up in the admin panel
- Added `Bundle\BundleFactory` class for creating instances of `Bundle` from submitted form data
- Added `Bundle\BundleProxy` class for lazy loading elements of a `Bundle` class
- Added `Bundle\Collection` class extending `Message\Cog\ValueObject\Collection`
- Added `Bundle\Create` class for saving new bundles to the database
- Added `Bundle\Edit` class for updating bundle data in the database
- Added `Bundle\Delete` class for soft-deleting bundles in the database
- Added `Bundle\Loader` class for loading bundles from the database
- Added `bundle\ProductRow` class for representing a product restraint on a bundle, i.e. the product, which options it applies to, and the quantity needed for the bundle to be valid
- Added `Bundle\Validator` class for checking that a bundle is still valid on an order
- Added `Bundle\EventListener` class to listen to changes to the basket and either add a bundle discount or remove it depending on its validity on the order
- Added `Bundle\Events` class holding constants for event names
- Added `Bundle\BundleImageCreate` class for saving bundle image assignments to the database
- Added `Bundle\BundlePriceCreate` class for saving bundle price settings to the database
- Added `Bundle\BundleProductCreate` class for saving bundle product rows to the database
- Added `Bundle\FileLoader` class for lazy loading images onto bundle
- Added `Bundle\PriceLoader` class for lazy loading prices onto bundle
- Added `Bundle\ProductRowLoader` class for lazy loading product rows onto bundle
- Added `Bundle\OrderDiscountFactory` class for creating discount order entities to assign to current order
- Added `Bundle\Exception\BundleBuildException` exception to be thrown when a bundle cannot be built from the submitted form data
- Added `Bundle\Exception\BundleValidationException` exception to be thrown when a bundle is no longer valid on an order
- Added `Bundle\Helpers\ItemCounterTrait` trait to add functionality to loop through product rows and return two arrays of the expected and current item counts for a bundle to be considered valid
- Added `Form\BundleForm` form for creating and editing bundles
- Added `Form\BundleProductForm` subform for adding product rows to a bundle
- Added `Form\DataTransformer\BundleTransformer` class for converting bundles to an array for populating the `Form\BundleForm`
- Added `Form\BundleProductSelector\ProductSelectorGroupForm` form for looping through expected items in a bundle and creating product selectors for each
- Added `Form\BundleProductSelector\ProductSelectorForm` subform for rendering a product selector for an item in a bundle
- Added `Field\Bundle` class allowing for bundles to be set against a `Message\Cog\Field\ContentTypeInterface`
- Added `Bundle` controllers to handle the CRUD functionality of bundles
- Added `Module\Bundle\ProductSelector` controller for rendering and handling a specialise product selector form for the bundle
- `Discount\Validator` class checks that no bundles with `allowCodes` set to false are on the order before adding a discount code to the order
- `Discount\Validator` counts the number of discount codes rather than all discounts on an order
- Added `bundle-listing.js` file for handling filterable table on bundle listing
- Added `discount_bundle` table to database
- Added `discount_bundle_price` table to database
- Added `discount_bundle_image` table to database
- Added `discount_bundle_product_row` table to database
- Added `discount_bundle_product_option` table to database

## 2.0.0

- Initial open source release