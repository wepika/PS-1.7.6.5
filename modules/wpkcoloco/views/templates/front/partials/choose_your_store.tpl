<br />
<h3>{l s='Choose your store'  d='Modules.Wpkcoloco.Choose_your_store'} *</h3>
<div class="form-row">
    <div class="form-group col-md-3">
        <input type="text" class="form-control" name="postal_code_search" id="postal_code_search"
               placeholder="{l s='Postal Code'  d='Modules.Wpkcoloco.Choose_your_store'}">
    </div>
    <div class="form-group col-md-3">
        <select name="country_search" id="country_search" class="form-control" required>
            {foreach $countries as $country}
                <option value="{$country.id_country}">{$country.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group col-md-3">
        <select name="wpkpickup_distance" class="form-control" id="range_search">
            <option value="10">10 km</option>
            <option value="20" selected="selected">20 km</option>
            <option value="50">50 km</option>
            <option value="100">100 km</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <button class="btn btn-primary" id="search_stores">{l s='Search'  d='Modules.Wpkcoloco.Choose_your_store'}</button>
    </div>
</div>

<div id="store_nearby">

</div>