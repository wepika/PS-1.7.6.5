<div class="row">
    <div class="col-md-3 col-sm-12 wpkcoloco">
        <h3>{l s='Your card'  d='Modules.Wpkcoloco.Your_card'}</h3>
        <div class="card">
            <img src="{$bg_card_info}"
                 alt="">
            <div class="slider-points">
                <span class="indicator" style="width: {$points[0]['idcpt']}%;">
                    <span class="total-points">
                        <span class="points">{$coloco_points}</span>
                        {l s='Points'  d='Modules.Wpkcoloco.Your_card'}
                    </span>
                </span>
            </div>
        </div>
        {l s='Card number'  d='Modules.Wpkcoloco.Your_card'} : {$coloco_card}
    </div>
    <div class="col-md-9 col-sm-12">
        <h3>{l s='Points'  d='Modules.Wpkcoloco.Your_card'}
            : {$coloco_points} {l s='Points'  d='Modules.Wpkcoloco.Your_card'}</h3>
        <hr/>
        <h3>{l s='WELCOME TO THE COLOCO CLUB'  d='Modules.Wpkcoloco.Your_card'}</h3>
        {if $is_valid}
            <p class="wpkcoloco_advantage">
                <span class="material-icons">
                    redeem
                </span>
                {l s='Take advantage of your 10% discount on your next purchase of min. 35€ in your favorite Tom&Co store. This offer is valid for two months from the date of registration.'  d='Modules.Wpkcoloco.Your_card'}
            </p>
        {else}
            <div class="alert alert-warning" role="alert">
                <p>{l s='If you want to take advantage of the Coloco benefits, please validate your address email.'  d='Modules.Wpkcoloco.Your_card'}
                    <a href="?resend_email=true">{l s='(I haven\'t received any mail)'  d='Modules.Wpkcoloco.Your_card'}</a>
                </p>
            </div>
        {/if}

    </div>
</div>