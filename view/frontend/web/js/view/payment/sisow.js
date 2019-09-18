/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
			{
                type: 'sisow_afterpay',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-afterpay'
            },
            {
                type: 'sisow_mistercash',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-mistercash'
            },
			{
                type: 'sisow_maestro',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-maestro'
            },
			{
                type: 'sisow_mastercard',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-mastercard'
            },
			{
                type: 'sisow_overboeking',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-overboeking'
            },
			{
                type: 'sisow_paypalec',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-paypalec'
            },
			{
                type: 'sisow_sofort',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-sofort'
            },
			{
                type: 'sisow_visa',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-visa'
            },
			{
                type: 'sisow_vvv',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-vvv'
            },
			{
                type: 'sisow_webshop',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-webshop'
            },
			{
                type: 'sisow_ideal',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-ideal'
            },
			{
                type: 'sisow_idealqr',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-idealqr'
            },
			{
                type: 'sisow_bunq',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-bunq'
            },
			{
                type: 'sisow_giropay',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-giropay'
            },
			{
                type: 'sisow_eps',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-eps'
            },
			{
                type: 'sisow_focum',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-focum'
            },
			{
                type: 'sisow_homepay',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-homepay'
            },
			{
                type: 'sisow_klarna',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-klarna'
            },
			{
                type: 'sisow_belfius',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-belfius'
            },
			{
                type: 'sisow_vpay',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-vpay'
            },
			{
                type: 'sisow_capayable',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-capayable'
            },
			{
                type: 'sisow_ebill',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-ebill'
            },
			{
                type: 'sisow_cbc',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-cbc'
            },
			{
                type: 'sisow_kbc',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-kbc'
            },
			{
                type: 'sisow_billink',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-billink'
            },
            {
                type: 'sisow_spraypay',
                component: 'Sisow_Payment/js/view/payment/method-renderer/sisow-spraypay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);