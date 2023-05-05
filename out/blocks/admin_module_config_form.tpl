[{if $oModule->getInfo('id') === "fcaddressvalidation"}]
    <div class="edittext">
        <input type="hidden" name="cl" value="bullshittest">
        <input type="submit" class="editinput">
    </div>
[{/if}]

[{$smarty.block.parent}]