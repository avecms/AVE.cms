# Metrica: Ecommerce

This optional module connects storefront activity to Yandex Metrica without
replacing the theme or changing prices and orders. Products and Commerce must
be installed and enabled. Cart tracking requires Commerce 1.8.10 or newer.

## Connect

1. Open **Modules → Metrica: Ecommerce → Settings** and enter your numeric
   counter ID. No account password or OAuth token is required.
2. Select **Already installed on the website** if a template, system block or
   tag manager already loads Metrica. Enable ecommerce on that counter and use
   the same data container, normally `dataLayer`.
3. Otherwise select **Load through this module**. The module loads and
   initializes Metrica. Webvisor is optional.
4. Enable the integration and keep test mode on. Select the events and save.
5. Visit the storefront in another tab, view a product, add it to the cart,
   change the quantity and remove it. Check the module's **Diagnostics** tab.
6. Disable test mode when ready. Verify actual receipt using Metrica tools.

An existing ecommerce sender must not send the same events as this module.
An existing counter alone is fine. The module never deletes template code.

## Events

- Impressions when at least half of a product card is visible; product clicks
  and detail views.
- Add/remove events use the actual quantity difference after a successful
  cart mutation. Failed or rolled-back changes are not purchases or additions.
- Purchase means a successfully created storefront order. Revenue is the
  discounted merchandise total, excluding shipping. Gifts have zero price;
  the order revenue includes any order-level coupon discount.
- Create the JavaScript goals `ave_begin_checkout` and
  `ave_payment_confirmed` in Metrica. Confirmed payment is a separate goal,
  never a second purchase.

Large orders are split to respect Metrica's 8192-character payload limit.
Parts use IDs such as `123-1` and `123-2`; revenue is divided, not duplicated.
Such an order counts as multiple purchases in Metrica reports.

## Diagnostics and limitations

Test mode does not push ecommerce into the live container or call Metrica
goals. An independently installed counter remains active. The browser-local
log retains the latest 50 events. Queued in JavaScript does not mean that
Yandex confirmed receipt. The log is not a delivery ledger.

Session queues, CSRF-protected acknowledgements and browser-side event IDs
reduce duplicates. Storage restrictions, ad blockers or closing a page can
still affect delivery; this is not an exactly-once server integration.

For external payment redirects, pending events are sent when the buyer comes
back in the same session. Events expire after 24 hours. Only this session's
new orders are watched for payment, for up to seven days and on subsequent
page visits or focus. No return means no browser payment goal. Guaranteed
server-side reporting requires a separate conversion import integration.
Manual and imported orders are not treated as storefront activity.

For custom cards use `data-product-card`, an inner `data-item` product ID,
`itemprop="name"` and `itemprop="price"` with numeric `content`. The standard
`.product-card` is also supported. Optional `data-metrica-list` labels a list.
AJAX cards are discovered automatically. The `commerce:cart-updated` event
and standard mini-cart replacement trigger a queue refresh.

Customer names, phones, emails, addresses, order access tokens and payment
credentials are not included. Configure your site's analytics consent policy
separately. Disabling this package does not remove a separately installed
counter. Uninstall uses the standard module lifecycle.

[Yandex payload reference](https://yandex.ru/support/metrica/en/ecommerce/data).
