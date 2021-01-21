{*<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="my_account_list_pets" href="{$link_to_pets_controller}">
          <span class="link-item">
               <i class="material-icons">pets</i>
            {l s='Pets' mod='wpkcoloco'}
          </span>
</a>*}

<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="my_account_list_pets" href="{$link_to_loyalty_card_controller}">
          <span class="link-item">
               <i class="material-icons">loyalty</i>
            {l s='My Coloco card' mod='wpkcoloco'}
          </span>
</a>

{if $link_to_tickets_controller}
<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="my_account_list_pets" href="{$link_to_tickets_controller}">
          <span class="link-item">
               <i class="material-icons">list_alt</i>
            {l s='Tickets' mod='wpkcoloco'}
          </span>
</a>
{/if}

{*<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="is_prod" href="#">*}
{*          <span class="link-item">*}
{*               {if WpkColoco::COLOCO_PROD_MODE == true}*}
{*                   PROD*}
{*               {else}*}
{*                   DEV*}
{*               {/if}*}
{*          </span>*}
{*</a>*}