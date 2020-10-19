## Changelog
5.4.14
- Added: Name/IBAN to order notification history message.

5.4.13
- Fix: Credit invoice
- Fix: Admin create order not loading

5.4.12
- Fix: Merged events

5.4.11
- Added: Setting to create invoice on order state (AfterPay and Klarna). 
- Added: Setting for Billink B2B to no longer require Gender and birthdate.
- Added: Setting to disable alternative Shipping address (AfterPay, Billink, Klarna and In3).
- Fix: Fee setting on multistore.

5.4.10
- Fix: Webshop Giftcard amount (capture)
- Fix: Fee Tax setting on multistore.
- Notify: Stop processing if Success state already set.
- Giropay: Content Security Policy (CSP).
- Payment logo css

5.4.9
- Replaced: Order_cancel_after event with order->cancel().

5.4.8
- Fix: Make invoice

5.4.7
- Fix: Notify with payment type 'overboeking'

5.4.6
- Fix: Payment fee sometimes not showing
- Added: Order_cancel_after event.

5.4.5
- Fix: multistore loads right Merchant info in admin order

5.4.4
- Added: option to log request and response for debugging

5.4.3
- Fix: payment failure on - in purchase ID

5.4.2
- Removed: deprecated functions
- Add: function to store payment fee to payment information

5.4.1
- Fixed: ING Home'pay now functioning
- Fixed: Capayble/IN3 now is also being display if it is the only method active
- Change: Image Capayable to IN3
- Change: Name Capayable to IN3

5.4.0
- Updated to new Klarna API
- Changed default name Spraypay

5.3.3
- Display fee inc/excl fee correct

5.3.2
- Better fooman validation

5.3.1
- fix: sisow class not loaded by objectManager anymore
- Set correct version number in composer.json
- added changelog to readme

5.3.0
- Added payment method spraypay
