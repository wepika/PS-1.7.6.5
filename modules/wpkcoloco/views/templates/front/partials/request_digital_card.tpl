<div class="container bg-light">
    <form action="" method="post" id="request_card_form">
        {if $customer_address}
            <input type="hidden" class="form-control" name="id_address" id="id_address"
                   value="{$customer_address.id_address}">
        {else}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="address">{l s='Address'  d='Modules.Wpkcoloco.Request_digital_card'} *</label>
                    <input type="text" class="form-control" name="address" id="address"
                           value="{Tools::getValue(address)}"
                           required>
                </div>
                <div class="form-group col-md-3">
                    <label for="number">{l s='Number'  d='Modules.Wpkcoloco.Request_digital_card'} *</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">n°</div>
                        </div>
                        <input type="number" class="form-control" name="number" id="number"
                               value="{Tools::getValue(number)}" required>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="box">{l s='Box'  d='Modules.Wpkcoloco.Request_digital_card'}</label>
                    <input type="text" class="form-control" name="box" id="box" value="{Tools::getValue(box)}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label for="city">{l s='City'  d='Modules.Wpkcoloco.Request_digital_card'} *</label>
                    <input type="text" class="form-control" name="city" id="city" value="{Tools::getValue(city)}"
                           required>
                </div>
                <div class="form-group col-md-3">
                    <label for="postcode">{l s='Postal Code'  d='Modules.Wpkcoloco.Request_digital_card'} *</label>
                    <input type="text" class="form-control" name="postcode" id="postcode"
                           value="{Tools::getValue('postcode')}" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="country">{l s='Country'  d='Modules.Wpkcoloco.Request_digital_card'} *</label>
                    <select name="country" id="country" class="form-control" required>
                        <option selected>{l s='Choose a country'  d='Modules.Wpkcoloco.Request_digital_card'}</option>
                        {foreach $countries as $country}
                            <option {if Tools::getValue(country) == $country.id_country} selected{/if}
                                    value="{$country.id_country}">{$country.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-row">
                <label for="phone">{l s='Phone number'  d='Modules.Wpkcoloco.Request_digital_card'}*</label>
                <input type="tel" class="form-control" name="phone" id="phone" maxlength="32"
                       value="{Tools::getValue(phone)}" required>
            </div>
        {/if}

        <div class="wpkcoloco">
            {include file=$module_template_path|cat:'partials/choose_your_store.tpl'}
        </div>

        <input type="hidden" class="form-control" name="request_a_card">
        <button type="submit" class="btn btn-primary" id="request_a_card">{l s='Request our card'  d='Modules.Wpkcoloco.Request_digital_card'}</button>


    </form>
</div>