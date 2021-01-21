<h3>{l s='Stores nearby'  d='Modules.Wpkcoloco.Stores_nearby'} *</h3>
<div class="wpkcoloco_stores_list">
    <table class="table table-bordered">
        <tbody>
        {foreach $stores as $store}
            <tr>
                <td class="wpkpickup_checkbox">
                    <input type="radio" class="wpkpickup_selected_store" name="wpkpickup_selected_store" value="{$store.id_store}"
                           id="wpkpickup_selected_store_{$store.id_store}"
                           {if $store.selected}checked="checked"{/if}
                    >
                </td>
                <td class="wpkpickup_store_name">
                    <label for="wpkpickup_selected_store_{$store.id_store}">
                        {$store.name} <span
                                style="font-weight: normal; font-size: 0.9em; color: #999"> ({$store.distance|round:1} km)</span>
                    </label>
                </td>
                <td class="wpkpickup_store_details wpkpickup_store_postcode">
                    <label for="wpkpickup_selected_store_{$store.id_store}" title="BE"
                           style="font-weight: normal; font-size: 0.9em; color: #999">
                        {$store.postcode}
                    </label>
                </td>
                <td class="wpkpickup_store_details wpkpickup_store_city">
                    <label for="wpkpickup_selected_store_{$store.id_store}"
                           style="font-weight: normal; font-size: 0.9em; color: #999">
                        {$store.city}
                    </label>
                </td>
            </tr>
        {/foreach}
        </tbody>

    </table>
</div>