{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Coloco Card' d='Modules.Wpkcoloco.Loyalty-card'}
{/block}

{block name='page_content'}

    {if isset($awaitingCustomer) && $awaitingCustomer}
        {include file=$module_template_path|cat:'partials/awaiting_customer.tpl'}
    {else}
        {if isset($cardType) && $cardType}
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    {include file=$module_template_path|cat:'partials/your_card.tpl'}
                </div>
            </div>
            <br
            / >
            <hr/>
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    {include file=$module_template_path|cat:'partials/disassociate_card.tpl'}
                </div>
            </div>
        {else}
            <p>{l s='Not yet a Coloco customer? Do you have a physical card? Enter it below or ask for a digital one, it takes 2 minutes.' d='Modules.Wpkcoloco.Loyalty-card'}</p>
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-linkcard-tab" data-toggle="tab" href="#nav-linkcard"
                       role="tab" aria-controls="nav-linkcard"
                       aria-selected="true">{l s='Link a Coloco card'  d='Modules.Wpkcoloco.Loyalty-card'}</a>
                    <a class="nav-item nav-link" id="nav-requestcard-tab" data-toggle="tab" href="#nav-requestcard"
                       role="tab" aria-controls="nav-requestcard"
                       aria-selected="false">{l s='Request a new Coloco card'  d='Modules.Wpkcoloco.Loyalty-card'}</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-linkcard" role="tabpanel"
                     aria-labelledby="nav-linkcard-tab">
                    <div class="wpkcoloco">
                        <h3>{l s='Link a Coloco card'  d='Modules.Wpkcoloco.Loyalty-card'}</h3>
                        {include file=$module_template_path|cat:'partials/add_physical_card.tpl'}
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-requestcard" role="tabpanel" aria-labelledby="nav-requestcard-tab">
                    <div class="wpkcoloco">
                        <h3>{l s='Request a new Coloco card'  d='Modules.Wpkcoloco.Loyalty-card'}</h3>
                        {include file=$module_template_path|cat:'partials/request_digital_card.tpl'}
                    </div>
                </div>
            </div>
        {/if}
    {/if}

{/block}