<!-- Modal awaiting -->
<div class="modal fade" id="wpkcoloco_awaiting_email" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{l s='An e-mail is waiting for you!' d='Modules.Wpkcoloco.Header'}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {l s='Nice to see you again' d='Modules.Wpkcoloco.Header'} {Context::getContext()->customer->firstname} ! <br/>
                {l s='We found a loyalty card linked to your account. An email was sent to the following address' d='Modules.Wpkcoloco.Header'}
                <strong>{Context::getContext()->customer->email}</strong> {l s='allowing you to link your e-shop account with your card!' d='Modules.Wpkcoloco.Header'}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{l s='Close' d='Modules.Wpkcoloco.Header'}</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal bind_card -->
<div class="modal fade" id="wpkcoloco_bind_card" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{l s='Take advantage of the Coloco benefits!' d='Modules.Wpkcoloco.Header'}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {l s='Nice to see you again' d='Modules.Wpkcoloco.Header'} {Context::getContext()->customer->firstname} ! <br/>
                {l s='Would you like to join our loyalty program? It allows you to have several discounts available in and out of store!' d='Modules.Wpkcoloco.Header'}
                <br/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{l s='Close' d='Modules.Wpkcoloco.Header'}</button>
                <a href="{Context::getContext()->link->getModuleLink('wpkcoloco', 'LoyaltyCard')}"
                   class="btn btn-primary">{l s='Take advantage of it now!' d='Modules.Wpkcoloco.Header'}</a>
            </div>
        </div>
    </div>
</div>