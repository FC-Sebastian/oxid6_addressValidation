[{if $oModule->getInfo('id') === "fcaddressvalidation"}]
    <div class="edittext">
        placehokder label
        <input type="file" class="editinput" name="fc_csvFile">
    </div>
    <div class="edittext">
        placeholder_label_delimiter
        <input type="text" class="editinput" name="fc[csv_separator]" value="[{$oView->fcGetSeparator()}]">
    </div>
    <div class="edittext">
        placeholder_label_enclosure
        <input type="text" class="editinput" name="fc[csv_enclosure]" value='[{$oView->fcGetEnclosure()}]'>
    </div>
    <div class="edittext">
        placeholder_label_escape
        <input type="text" class="editinput" name="fc[csv_escape]" value="[{$oView->fcGetEscape()}]">
    </div>
    <div class="edittext">
        <input type="submit" class="editinput">
    </div>

    <script type="text/javascript">
        document.getElementById("moduleConfiguration").setAttribute('enctype', 'multipart/form-data');
    </script>
[{/if}]

[{$smarty.block.parent}]