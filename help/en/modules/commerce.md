# Cart, delivery and payment

The Commerce module manages the cart, checkout, delivery, payment,
coupons, favorites, viewed products and customer order history.

## Processing archived carts

In Orders → Abandoned carts, choose the contact or UTM archive. After handling
the customer, click the green Processed checkmark and confirm. The cart leaves
the pending archive queue and remains available under Processed archive.
Contacts, cart items and attribution are retained without automatic expiry.
The detail view shows when and by whom it was processed. Reopen returns it to
the queue. The mark is shared by all managers and requires order management permission.

## Order export

Set the search, order state, and payment filter, then click **CSV**. The current
filtered result is streamed in chunks of 500 rows and the operation is recorded
in the audit log.

## Cart promotions

Open `Store -> Settings -> Promotions` to create automatic rules that do not
require a promo code. A rule can discount a related product or add a real
catalog product as a free gift.

1. Under **When to apply**, select products, catalog sections, or any cart
   item. Set the required quantity and optional minimum cart amount.
2. Under **Customer reward**, select a percentage discount, fixed discount,
   fixed price, or gift.
3. Select the discounted products or sections. For a gift, find one catalog
   product.
4. Optionally set a date range, priority, and maximum uses per cart.
5. Enable **Allow coupon** only when a promo code may be combined with the
   rule.

Each complete condition set rewards the configured number of items. When rules
overlap, a product line receives the best discount. A gift is stored as a real
order item with a zero price, participates in shipment packaging, and
automatically disappears when the condition is no longer met. Customers cannot
change or remove a generated gift.

The order snapshot stores original and final prices, the promotion name, and
the gift marker. The usage journal keeps this information even when the rule is
later changed or deleted.

A fixed-price promotion between specific products is also exposed as a bundle
offer on both public product pages. The condition product page shows eligible
discounted products, while the discounted product page shows the products that
activate its special price. **Add bundle to cart** submits both products, and the server
revalidates the active promotion before applying the discount.

The block is rendered by `[mod_product_bundles]`. Use
`[mod_product_bundles:offers]` for promotional pairs only and
`[mod_product_bundles:manual]` for manual bundles only. Its markup is editable
under `Products -> Templates -> Product bundles`. `promotion_offers` contains offers
derived from active promotions, while `bundles` contains manually configured
product bundles, so the storefront markup does not require a PHP or theme
override.

## Online payment journal

The **Payment journal** tab shows the complete gateway sequence: payment
creation, transaction assignment, callback validation, confirmation, errors
and refunds. The same events are shown in the selected order under
**Payment history**.

If a callback was not delivered, open the order and click **Check payment**.
The button is available only when an online transaction exists and the
selected gateway supports status queries. Commerce asks the provider directly
and verifies the transaction, amount, and currency before marking the order as
paid. Repeated checks are safe and dispatch the completed-payment event only
once.

The journal is designed for investigating disputed payments without server-log
access. It never stores API credentials, signatures, customer email or phone,
complete callbacks, or complete bank responses. It stores only the order,
gateway, transaction, stage, status, amount, currency, safe provider code and
timestamp.

Journal write failures do not interrupt a payment request. Apply the Commerce
module migration after updating so that its journal table is created.

## Saved order filters

Above the orders table is a list of **Saved Views**. He is needed
for recurring work queues, such as “New Unpaid” or “Orders
for a return call."

1. Set up search, status and payment attribute using regular filters.
2. Click the button with a bookmark and a plus sign.
3. Enter a friendly name and save.
4. Next time, select this name in the list - the filters will be applied immediately.

If you save another set with the same name, the old view
will be updated. The Trash button deletes the selected set. Personal performances:
other employees do not see them, and the orders and store settings themselves do not
change.

## Order created by manager

An employee with the right `manage_orders` can create an order without a public cart:

1. Open `Orders` and click **Create Order** to the right of the header.
2. Find an existing site user by name, phone, email or ID.
   His profile will be linked to the order, and his contacts will be filled in automatically.
   The form also shows the customer's order count, total, and recent orders.
   If the buyer has not yet registered, fill in the name and phone number or email
   manually.
3. Find products by name, SKU or ID. Each product variant is
   as a separate item, so the order will include the correct article number, URL and
   characteristics of the option.
4. Specify quantity. The price field can be changed: the new amount is valid only
   inside this order and does not change the price of the product in the catalog.
5. Active promotions are previewed automatically. A matching bed and mattress,
   for example, can discount the mattress or add a configured gift.
   A configured product bundle can be added to the order in one action.
6. If necessary, set a separate manager discount, delivery, payment, status
   and payment state. Promotions are applied first, then the manager discount,
   followed by delivery and payment surcharges.
   The helper warns about incompatible delivery and payment methods and products
   whose shipping packages are not configured.
7. After saving, the order card will open. It shows catalog and manual
   price, discount, author of creation and history of actions.

The order stores a snapshot of the position at the time of creation: document ID, name,
article, image, option, catalog price, manager price, quantity and
amount. Subsequent product changes do not overwrite this snapshot. Choice
**Send Email** triggers regular Commerce notifications after successful
creating an order; The payment transition does not open automatically.

The server recalculates promotions during save, so the browser preview cannot
be used to submit a forged total. Applied promotion and gift snapshots are
stored exactly as they are for orders created from the public cart.

The **Customer link** button in a saved order copies its public result URL. The
URL contains the random order token instead of the database record ID.

## Changing the composition of an order

In the saved order card, click **Change contents**. The editor allows you to:

- find and add a new product or specific option;
- change the quantity and price of an individual item;
- delete unnecessary position;
- set the manager’s general discount;
- see the new total before saving.The editor saves information that has already been included in the order. Name, article,
the image and characteristics of the old item are not replaced by data from the catalog.
This is important for the story: the order continues to show exactly the product that
the buyer chose at the time of registration. Current catalog data is used
only for new positions.

Coupons and other previously calculated discounts are shown separately and not
are overwritten by the manager's discount. Delivery costs and surcharges also
are saved. After the change, Commerce recalculates the item total and grand total.

Each save goes into the order history and general audit. There you can see what
items have been added, deleted or changed. The composition cannot be changed
paid or canceled order, as well as after creating a payment
transactions. This protects already recorded calculations.

Modules can test or extend an operation via hooks:

- `commerce.order.composition_updating` — before recording;
- `commerce.order.composition_updated` - after successful saving.

The order, previous and new composition, calculated amounts,
exact difference and employee ID.

## Mini-cart in a website template

Insert the desired tag into the general site template, system block or other place where
public tags AVE.cms are processed:

| Tag | What does |
| --- | --- |
| `[mod_basket]` | Mini-cart: number of items, amount and link to cart |
| `[mod_basket:mini]` | Same as `[mod_basket]` |
| `[mod_basket:fav]` | Compact link and favorites counter |
| `[mod_basket:viewed]` | Compact link and counter of viewed products |

The HTML of these elements is edited in `Store -> Settings -> Templates`:
`mini.twig`, `favorites_mini.twig` and `viewed_mini.twig`. There are also
templates for full cart, checkout, favorites and order history.

The editor works with the same file that the public site uses. If
the active theme overrides the template in
`templates/<theme>/views/system_basket/`, it is the theme file that is edited; in
in other cases - a system file from
`modules/commerce/app/Basket/view/`. Template card shows source and
way. Once saved, the Twig cache is cleared so changes are applied immediately.

## Twig template data

In the editor of each template there is a drop-down block “Available data”.
Clicking on a variable inserts it into the current CodeMirror position. Twig by
escapes HTML by default. Apply filter `|raw` only to prepared
markup engine, for example `{{ product_cards.html|raw }}`.

In all public templates, except letters, customized addresses are available:

- `{{ basket_urls.cart }}` — basket;
- `{{ basket_urls.checkout }}` — design;
- `{{ basket_urls.favorites }}` - favorites;
- `{{ basket_urls.viewed }}` — viewed products;
- `{{ basket_urls.orders }}` - order history.The object `basket_pages` is also available. For example,
`{{ basket_pages.cart.title }}` Displays the cart page title. Each
pages `cart`, `checkout`, `favorites`, `viewed` and `orders` have properties
`path`, `title`, `description` and `template_id`.

### Mini basket: `mini.twig`

- `{{ basket.quantity }}` — total number of goods;
- `{{ basket.total }}` — amount of goods.
- `{{ basket.total_order }}` — total after promotions and coupon.

### Product added: `added.twig`

Object `product`: `id`, `name`, `article`, `alias`, `image`, `price`,
`quantity`, `amount`.

Example: `{{ product.name }}` and
`{{ product.price|number_format(0, '.', ' ') }}`.

### Cart: `cart.twig`

- `basket.products` — array of product items;
- `basket.quantity` — total quantity;
- `basket.gift_quantity` — generated gift quantity;
- `basket.total` — amount without discount;
- `basket.promotion_discount` — automatic promotion discount;
- `basket.coupon_discount` — coupon discount;
- `basket.discount` — total discount;
- `basket.total_order` - total after all discounts;
- `basket.promotions` — applied promotion rules;
- `basket.coupon_blocked` — the current promotion disallows coupons.

Fields `product.*` are available inside the loop:

```twig
{% for product in basket.products %}
  {{ product.name }} — {{ product.quantity }} × {{ product.price }}
{% endfor %}
```

In addition to the values shown in the example, `product.hash` is available for changing and
deleting a position and `product.price_field_id` for buttons to add a product.
Promotion templates can also use `product.base_price`, `product.base_amount`,
`product.final_price`, `product.final_amount`, `product.promotion_title`, and
`product.is_gift`. Gift final price and amount are zero.

### Design: `checkout.twig`

- `csrf` — form token;
- `basket.*` and `basket.products` - current cart;
- `basket.shipment.ready` — are the packages ready for calculation;
- `basket.shipment.summary.places` — number of cargo pieces;
- `basket.shipment.summary.weight_kg` — total weight;
- `basket.shipment.summary.volume_m3` — total volume;
- `options.delivery` — delivery methods;
- `options.payment` — payment methods;
- `options.payment_enabled` — is payment available;
- `options.show_basket` — is it necessary to show the composition;
- `values.first_name`, `values.phone`, `values.email` and other entered fields;
- `values.last_name`, `values.region`, `values.city`, `values.postcode`,
  `values.address`, `values.description` — other buyer fields;
- `errors._form`, `errors.first_name`, `errors.phone`, `errors.email`,
  `errors.city` - errors.
- `errors.account` — login or registration error from registration;
- `account.logged_in` and `account.user` - the status of the current buyer;
- `account.registration.enabled`, `policy_url`, `policy_label` - possibility
  create an account and mandatory consent data;
- `account.login_url`, `account.remember_url`, `account.profile_url` - addresses
  login, password and profile recovery.

In the cycle `options.delivery`, method fields are available, for example
`item.id`, `item.delivery_title`, `item.delivery_price`. In a loop
`options.payment` - `item.id`, `item.payment_title`,
`item.payment_description`, `item.payment_delivery`. In a loop
`basket.shipment.missing_products` available `item.product_id` and `item.title`.

### Successful order

`success.twig` and `one_click_success.twig` receive the object `order`. Basic
fields: `order_id`, `order_total`, `order_first_name`, `order_last_name`,
`order_phone`, `order_email`, `delivery_title`, `payment_title`,
`order_description`.

### One-click purchase: `one_click.twig`

`csrf` and object `product` are available with the same fields as in `added.twig`.

### Favorites and Viewed

`favorites.twig` and `viewed.twig` receive:

- `products` — array of products;
- `products_count_label` - finished quantity with the correct declination,
  for example `1 product` or `12 products`;
- `product_cards.handled` — a sign that the product module has prepared cards;
- `product_cards.html` — ready-made card markings;
- `product.*` inside the loop `products`.

If card design is controlled by a product module, use:

```twig
{% if product_cards.handled %}
  {{ product_cards.html|raw }}
{% else %}
  {# собственный цикл products #}
{% endif %}
```

`favorites_mini.twig` and `viewed_mini.twig` receive only `{{ count }}`.

The Favorites page supports bulk AJAX actions. Button with attribute
`data-favorites-add-all` adds to the cart only those available products that
not there yet. Pressing it again does not increase their number. Button
`data-favorites-clear` clears the list and immediately updates the page and counter in
header without rebooting.

The history of viewed products is filled in automatically when you open a public
cards. Products calls hook `catalog.product.viewed` and Commerce saves
ID in the session and, for the logged-in buyer, in his account. If history is disabled
in the Commerce settings, the hook remains safe and does not affect the product display.
A button with the `data-viewed-clear` attribute clears the history with an AJAX request and immediately
updates the page and the counter in the header.

Commerce also supplements the general view model of the product card with the property
`item.favorite`. This allows the published card template to save
button state after reboot:

```twig
<button aria-pressed="{{ item.favorite ? 'true' : 'false' }}">
  {{ item.favorite ? 'Удалить из избранного' : 'В избранное' }}
</button>
```

### History and order page

`orders.twig` receives the array `orders`. Within the cycle, `order.id`,
`order.order_id`, `order.order_type`, `order.order_published`,
`order.status_name`, `order.status_color`, `order.order_pay`,
`order.order_total`, `order.items_count`, array of up to four saved
images `order.product_thumbs` and the number of other positions
`order.products_more`.

`order.twig` receives one object `order`, its array `order.products` and `csrf`
for repayment. The order item has `name`, `title`, `price`,
`quantity`, `amount` and normalized `image`. Order also available
`status_tone` (`new`, `progress`, `done`, `cancel`), current stage number
`status_step` from 0 to 3 and the sign `status_cancelled`.

`order_not_found.twig` does not receive special data: it is a static page,
but the configured `basket_urls.*` are available in it.

### Letters

`mail_admin.twig` and `mail_client.twig` receive `order` and `settings`. Except
order fields available `{{ settings.from_name }}` and
`{{ settings.from_email }}`.
Public `basket_urls.*` are not intentionally added to letters.

## Delivery services

1. Open `Store -> Settings -> Delivery`.
2. In the “Payment Services” block, open the desired carrier and specify the API keys,
   sender's city or postal code.
3. Enable calculation and save the service.
4. In the “Delivery methods” block, create or open a method for receiving the order.
5. Select the payment service and the desired type of delivery: courier, pickup point or terminal.

Assigning a service to a shipping method does not automatically enable it. To
the calculation worked, the required details must be filled in the service card
and the “Enable calculation” switch is turned on. Secret values after saving
not shown; an empty field leaves the same secret when saving again.
The “Delete details” button deletes the settings of the selected carrier.

SDEK, PEK, Boxberry and Business Lines are available. The calculation is for reference and
does not in itself change the outcome of the order. Fixed cost shipping method
continues to work regardless of the API.

On the checkout page, the calculation starts automatically after selecting a method
delivery and filling in the city or zip code. Below the name of the method are shown
carrier's estimated price and deadline. This amount is informational: in total
The order still includes only the cost specified for the delivery method.

SDEK can calculate the tariff by city or zip code, Boxberry - courier tariff
by index. For PEC, Business Lines and selection of a specific pickup point, public form
must additionally transmit the internal code of the service destination:
`pecom_city_id`, `dellin_kladr` or `boxberry_pvz`. These fields can be added to
own `checkout.twig`; the engine already receives them and transmits them to gateway.

## Payment servicesPSB and YooKassa are installed as separate modules. Their keys are set to
page of the corresponding module.

To associate a service with a payment option:

1. Open `Store -> Settings -> Payment`.
2. Make sure the service is marked as connected. Arrow button opens
   his details.
3. Open the payment method and select the payment service.
4. If necessary, limit your payment method to your selected shipping methods.

If the service is not selected, the method is considered payment without online transfer: for example,
in cash, by card upon receipt or by bank transfer.

## Abandoned carts and archive

Open `Store -> Orders -> Abandoned carts` and select **Archive with contacts**
in the state filter above the table. The view button opens the saved customer
details, cart contents and traffic attribution, including any captured UTM tags.

After three days without activity, carts with saved customer data move to the
archive during cleanup. **Process overdue carts** runs this operation manually.
Archived records are exempt from the regular cart retention period.

**Path to order** covers sessions created in the last 30 days, including those
that resulted in an order. The table filters do not restrict these counters.
The contact counter records entry of a first name, last name, email or phone.
Choosing delivery or payment does not imply that contacts were entered, and an
empty phone mask does not count. Partial input counts, so this is not the number
of completed forms or customers with a callable phone number.

## Payment Attempts And Order Statuses

Starting payment again first checks the existing attempt. A pending payment
reuses its confirmation link; an ambiguous network failure reuses its original
idempotency key. A new attempt starts after a confirmed cancellation. Previous
attempts retain their order association. A late pending response cannot remove
a confirmed paid flag. Cancelled orders cannot start payment.

An order with an ongoing payment cannot have its amount or contents changed
until that payment is checked. Each gateway declares additional required
customer fields, such as email. Checkout and online payments currently use RUB;
the feed currency neither changes the order currency nor converts its prices.

Under `Shop -> Settings -> Statuses`, assign each status a stable purpose:
new, processing, shipped, completed, cancelled, or other. Renaming a status does
not change its behavior. Review the purpose of custom statuses after upgrading.

## Checkout And Archived Carts

Checkout checks current product publication and prices. Changed prices require
the customer to confirm the recalculated cart; unavailable items must be removed.
Submitting the same checkout again returns the existing order instead of
creating a duplicate.

The abandoned-cart table provides pagination while retaining filters and sort
direction. Retention applies to the cart as a whole, so older lines do not expire
individually in an active cart. Adding a product after checkout or archiving
starts a new cart without overwriting the previous contact and traffic snapshot.

## Public pages

Addresses for cart, checkout, favorites, viewed items and personal orders
are configured in `Store -> Settings -> Public pages`. For every page
You can choose a general site template. Commerce substitutes page content into
its `[tag:maincontent]`.

## Login and registration at checkout

Commerce uses a common public user system rather than creating
individual “basket shoppers.”

1. If the visitor has already logged in, the new order immediately receives his ID and appears in
   order history.
2. If the entered email is already registered, the form prompts you to log in directly to
   checkout page. After logging in, the current cart is saved, despite
   change session ID.
3. For a new email, an optional switch to create a personal one is shown
   office. Consent to the policy becomes mandatory only after enabling
   this switch.
4. After ordering, the account is immediately opened in the current browser, and the name data,
   phone number, city, address and zip code become the initial profile data.
5. The permanent password is not sent by mail. The buyer receives a one-time
   link and sets the password yourself. If the letter has not arrived, you can access
   restore using the usual “I don’t remember my password” form.

The script is included in `System -> Site Users -> Registration`, in the block
**Account after order**. The consent text, policy address and Twig-
letter template. If public registration or this script is disabled,
checkout continues to work in guest mode.
# Payment after checkout

An online order is saved before starting payment. The confirmation page shows
its number and amount. The Pay button is immediately available; manager approval
is not required. Repeated clicks reuse the existing payment.

In **Shop settings → General → Payment notice**, edit the notice and toggle its
visibility. Hiding the notice does not hide the Pay button. Its presentation is
editable in the `payment_notice` shop template.
