[{if $oModule->getInfo('id') === "fcaddressvalidation"}]

    [{if $oView->fcGetTypeInvalid() === true}]
        <div class="errorbox">[{oxmultilang ident='FCADDRESSVALIDATION_TYPE'}]</div>
    [{/if}]

    [{if $oView->fcGetHeadersInvalid() === true}]
        <div class="errorbox">[{oxmultilang ident='FCADDRESSVALIDATION_HEADERS'}]</div>
    [{/if}]

    [{if $oView->fcGetComplete() === true}]
        <div class="messagebox">[{oxmultilang ident='FCADDRESSVALIDATION_COMPLETE'}]</div>
    [{/if}]

    <table>
        <tr>
            <td>
                <div class="edittext">
                    [{oxmultilang ident='FCADDRESSVALIDATION_FILE'}]
                </div>
            </td>
            <td>
                <div class="editinput">
                    <input type="file" name="fc_csvFile">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="edittext">
                    [{oxmultilang ident='FCADDRESSVALIDATION_SEPARATOR'}]
                </div>
            </td>
            <td>
                <div class="editinput">
                    <input type="text" name="fc[csv_separator]" value="[{$oView->fcGetSeparator()}]">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="edittext">
                    [{oxmultilang ident='FCADDRESSVALIDATION_ENCLOSURE'}]
                </div>
            </td>
            <td>
                <div class="editinput">
                    <input type="text" name="fc[csv_enclosure]" value='[{$oView->fcGetEnclosure()}]'>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="edittext">
                    [{oxmultilang ident='FCADDRESSVALIDATION_ESCAPE'}]
                </div>
            </td>
            <td>
                <div class="editinput">
                    <input type="text" name="fc[csv_escape]" value="[{$oView->fcGetEscape()}]">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="editinput">
                    <input type="submit">
                </div>
            </td>
        </tr>
    </table>



    <div class="edittext">
        <span>[{oxmultilang ident='FCADDRESSVALIDATION_COUNT'}][{$oView->fcGetAddressCount()}]</span>
    </div>

    <script type="text/javascript">
        document.getElementById("moduleConfiguration").setAttribute('enctype', 'multipart/form-data');
    </script>
[{/if}]

[{$smarty.block.parent}]