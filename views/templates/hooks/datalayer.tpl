<!-- Google Tag Manager Data Layer -->
<script>
window.dataLayer = window.dataLayer || [];
{if isset($transactionId)}
dataLayer.push({
    'event':'ConfirmacionDeCompra',
    'type_of_customer' : '{$type_of_customer}',
    'ecommerce' : {
        'purchase' : {
            'actionField' : {
                'id': '{$transactionId}',
                'revenue' : '{$transactionTotal|string_format:"%.2f"}',
                'shipping': '{$transactionShipping|string_format:"%.2f"}',
                },
                'products' : [
                             {foreach from=$transactionProducts item=product}
                                {literal}
                                    {
                                {/literal}
                                'sku' : '{$product['id_product']}',
                                'name' : '{$product['name']}',
                                'category' : '{$product['category']}',
                                'price' : '{$product['price_wt']|string_format:"%.2f"}',
                                'quantity' : '{$product['quantity']}'
                                {literal}
                                    }
                                {/literal}{if not $product@last}, {/if}
                                {/foreach}
                            ]
                }
        }
    }
);
{/if}
dataLayer.push({
    'PageType':'{$page_name}'}
);
</script>
<!-- End Google Tag Manager Data Layer -->