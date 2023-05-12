[{assign var="class" value=$oView|get_class}]

[{if $class === 'OxidEsales\Eshop\Application\Controller\UserController'}]
    <input id="fcBillingHidden" type="hidden">
    <script type="text/javascript">
        let fcBaseUrl = '[{$oViewConf->getSelfActionLink()}]';
    </script>
    [{oxscript include=$oViewConf->getModuleUrl('fcaddressvalidation', 'out/src/js/fcaddressvalidation.js')}]
    [{oxstyle include=$oViewConf->getModuleUrl('fcaddressvalidation', 'out/src/css/fcaddressvalidation.css')}]
[{/if}]

[{$smarty.block.parent}]