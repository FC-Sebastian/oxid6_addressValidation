[{assign var="class" value=$oView|get_class}]

[{if $class === 'OxidEsales\Eshop\Application\Controller\UserController'}]
    <input id="fcBillingHidden" type="hidden">
    <script type="text/javascript">
        let fcBaseUrl = '[{$oViewConf->getSelfActionLink()}]';
        let fcErrorMsgNoZip = `[{oxmultilang ident='FCADDRESSVALIDATION_ERROR_NOZIP'}]`;
        let fcErrorMsgZipHint = `[{oxmultilang ident='FCADDRESSVALIDATION_ERROR_ZIPHINT'}]`;
        let fcErrorMsgCountry = `[{oxmultilang ident= 'FCADDRESSVALIDATION_ERROR_COUNTRY'}]`;
    </script>
    [{oxscript include=$oViewConf->getModuleUrl('fcaddressvalidation', 'out/src/js/fcaddressvalidation.js')}]
    [{oxstyle include=$oViewConf->getModuleUrl('fcaddressvalidation', 'out/src/css/fcaddressvalidation.css')}]
[{/if}]

[{$smarty.block.parent}]