# OrderEdit 2.0.35

# With this module you can modify and alter every existing order in your store, also after the purchase 
# is completed. Product information, as well as shipping, payment and discounts data can be modified. For 
# more information on how the module works, please refer to the screens, video, demoshop, or contact us 
# directly.

## Changelog
#### 2.0.35 (10.05.2022):
* Fixed the common calculation when adding a discount (`order_detail_tax` table)

#### 2.0.34 (26.04.2022):
* Added documents date changing
* Added order history statuses changing
* Added the order payment changing
* Fixed OrderCartRules calculation

#### 2.0.33 (12.04.2022):
* Fixed saving payments (`9:53:26 when zero is missing`)

#### 2.0.32 (06.04.2022):
* Fixed total weight calculation
* Fixed getting the carrier tax rate

#### 2.0.31 (02.03.2022):
* Fixed Id lang parameter for order message until PS 1.7.7.6

#### 2.0.30 (10.02.2022):
* Fixed the jquery binding to elements in the order page

#### 2.0.29 (05.02.2022):
* Fixed the language of the customer message in the order

#### 2.0.28 (28.01.2022):
* Auto date for payments fixed

#### 2.0.27 (25.12.2021):
* A little the code refactoring
* Added PS bugfix about autocomplete error (`$.browser`)

#### 2.0.26 (05.11.2021):
* Moved the add product action to the lightbox
* Added getShippingAction() function to the compatibility with PS 1.7.7.2
* `Teatarea` for the payment order node instead of `input` field

#### 2.0.25 (18.10.2021):
* Corrected product calculations

#### 2.0.24 (14.10.2021):
* New translations system
* Corrected stock quantity control when changing the quantity of products in an order

#### 2.0.23 (06.10.2021):
* Fixed the searched order link
* Added new fields to the search query (id_order, firstname, lastname)

#### 2.0.22 (04.10.2021):
* Fixed display of carrier tax

#### 2.0.21 (10.09.2021):
* Fixed the currency change in the order.

#### 2.0.20 (25.06.2021):
* Removed keyup handler from the carrier cost calculation

#### 2.0.19 (28.05.2021):
* Fixed saving the name of the payment module after re-creating the payment

#### 2.0.18 (03.05.2021):
* Fixed shipping price calculating

#### 2.0.17 (28.04.2021):
* Fixed shipping methods for changing (added module's carriers)

#### 2.0.16 (06.04.2021):
* Fixed shipping in the order calculation
* Fixed common tax for all order
* Fixed the modal view if products is digital

#### 2.0.15 (30.03.2021):
* Stock control added for changes in product quantities

#### 2.0.14 (30.03.2021): - Only for PS 1.7.7.0
#### 2.0.13 (12.03.2021):
* Fixed the payment method removing
* Added function to delete the carrier at all (working when carrier amount is zero only)
* The payment can be negative since now

#### 2.0.12 (02.03.2021):
* Fixed registering admin controllers

#### 2.0.11 (22.02.2021):
* Fixed saving the total invoice amount when changing the cost of transport

#### 2.0.10 (18.02.2021):
* Fixed shipping editor

#### 2.0.9 (11.02.2021):
* Added wrapping editor block
* Fixed total calculation if order has discounts
* Added changing customer of order

#### 2.0.8 (29.01.2021):
* Added history log of modified orders
* Added a message that will be sent to the customer along with an indication of the change order surcharge

#### 2.0.7 (25.01.2021):
* HTML moved in a separate template

#### 2.0.6 (21.01.2021):
* Added more shipping fields to edit

#### 2.0.5 (19.01.2021):
* Fixed assets loading only in the AdminOrders page
* Added `OrderEditAddProductToOrderHandler` decorated services for adding product 

#### 2.0.4 (18.01.2021):
* Added `add product` action to the paid and delivered orders
* Fixed the total carrier weight after the product weight changing

#### 2.0.3 (16.01.2021):
* Missing templates added

#### 2.0.2 (15.01.2021):
* Added customer email re-sending after the order wes changed

#### 2.0.1 (06.01.2021):
* Added autoload composer's controller for `Decorate` the native one

#### 2.0.0 (04.01.2021):
* Init new version since PS 1.7.7.*
