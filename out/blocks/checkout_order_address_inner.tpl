[{$smarty.block.parent}]

<div id="fcMap" style="height: 500px"></div>

<script type="text/javascript">
    [{if $oDelAdress}]
        let fcMapAddress = '[{$oDelAdress->oxaddress__oxstreet->value}] [{$oDelAdress->oxaddress__oxstreetnr->value}]';
            fcMapAddress += ' [{$delivadr->oxaddress__oxzip->value}] [{$delivadr->oxaddress__oxcity->value}]';
            fcMapAddress += ' [{$delivadr->oxaddress__oxcountry->value}]';

    [{else}]
        let fcMapAddress = '[{$oxcmp_user->oxuser__oxstreet->value}] [{$oxcmp_user->oxuser__oxstreetnr->value}]';
            fcMapAddress += ' [{$oxcmp_user->oxuser__oxzip->value}] [{$oxcmp_user->oxuser__oxcity->value}]';
            fcMapAddress += ' [{$oxcmp_user->oxuser__oxcountry->value}]';
    [{/if}]
</script>
[{oxscript include=$oViewConf->getModuleUrl('fcaddressvalidation', 'out/src/js/fcMap.js')}]
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDm_dVkwNA4tJF96p98q-iz6h7v7xxHx3A&libraries=places&callback=fcInitMap" defer></script>
