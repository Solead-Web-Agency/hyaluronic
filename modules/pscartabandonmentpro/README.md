# pscartabandonmentpro
This module sends email to customers each time they add products to their shopping cart but do not purchase. It reminds and encourages them to complete the order. It’s beneficial and easy to use!

-   Automatically send up to 5 personalized emails to remind your customers about the contents of their cart. This helps turn abandoned carts into sales.
-   Easily create email templates tailored to match your online store’s theme.
-   Use a free external service to make the sendings automatic
-   Target different customer profiles (active, inactive or without order) to adapt your email

Also, **you can now propose discounts directly via the email** in order to remind your clients to finalize their purchases.  
  
Abandoned Cart reminder Pro allows to easily add **discounts on a product price** or **free shipping cost** directly on the email depending on each clients cart amount.

# Change templates
Change cart view:
`/pscartabandonmentpro/views/templates/admin/ajax/cart.tpl`
Change Live Edit view:
`/pscartabandonmentpro/views/templates/admin/email/`
Change sent emails:
`/pscartabandonmentpro/mails/`

No new templates can be created easily.  It is easier to override the existing templates.

# Test the reminders
Go into : `/pscartabandonmentpro/controllers/front/FrontCAPCronJob.php`
Set **$debug** to `true`

It prevents sending emails in case the module is in production. It will then be possible to debug the module in front safely.

# Statistics
The statistics do not take into account the discounts when buying. It takes the most expensive price for a given product.