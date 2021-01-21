{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Account Verification' d='Modules.Wpkcoloco.Accountverification'}
{/block}

{block name='page_content'}
    <div class="row">
        {if isset($account_verified) && $account_verified}
            <p>{l s='Congratulations! Your Coloco card account has been successfully linked to your account.' d='Modules.Wpkcoloco.Accountverification'}</p>
        {elseif isset($ps_coloco_conflict) && $ps_coloco_conflict}
            <div class="col-xs-12">
                <p>{l s='It appears that the name on your card does not match what is on your account.' d='Modules.Wpkcoloco.Accountverification'}</p>
                <p>{l s='Please choose the right information for us to update.' d='Modules.Wpkcoloco.Accountverification'}</p>
            </div>
            <div class="col-xs-12">
                <form method="post" action="{$formLink}">
                    <div class="row">
                        <div class="col-md-6 col-xs-12">
                            <article class="address">
                                <div class="address-header h4 form-check">
                                    <input class="form-check-input" type="radio" name="chosen_information"
                                           id="chosen_information_ps" value="ps"/>
                                    <label class="form-check-label" for="chosen_information_ps">
                                        {l s='Your Account Information' d='Modules.Wpkcoloco.Accountverification'}
                                    </label>
                                </div>
                                <div class="address-body">
                                    {$psCustomer->firstname} {$psCustomer->lastname}<br>
                                    {if $psAddress !== null}
                                        {$psAddress->address1}
                                        <br/>
                                        {$psAddress->postcode} {$psAddress->city}
                                        <br/>
                                        {$psCountryName}
                                    {else}
                                        {l s='No address found' d='Modules.Wpkcoloco.Accountverification'}
                                    {/if}
                                </div>
                            </article>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <article class="address">
                                <div class="address-header h4 form-check">
                                    <input class="form-check-input" type="radio" name="chosen_information"
                                           id="chosen_information_coloco" value="coloco"/>
                                    <label class="form-check-label" for="chosen_information_coloco">
                                        {l s='Your card information' d='Modules.Wpkcoloco.Accountverification'}
                                    </label>
                                </div>
                                <div class="address-body">
                                    {$colocoCustomer->getPrenom()} {$colocoCustomer->getNom()}<br>
                                    {if empty($colocoCustomer->getAdr1()) || !$colocoCustomer->getAdr1()}
                                        {l s='No address found' d='Modules.Wpkcoloco.Accountverification'}
                                    {else}
                                        {$colocoCustomer->getAdr1()} {$colocoCustomer->getAdr2()}<br>
                                        {$colocoCustomer->getCp()} {$colocoCustomer->getVille()}<br>
                                        {$colocoCustomer->getPays()}
                                    {/if}
                                </div>
                            </article>
                        </div>
                    </div>
                    <br/>
                    <button class="btn btn-primary form-control-submit" type="submit" name="chooseBetweenAccount">
                        {l s='Save' d='Modules.Wpkcoloco.Accountverification'}
                    </button>
                </form>
            </div>
        {elseif isset($gotError) && $gotError}
            <p>{l s='An error occurred during the process. Please contact an administrator if the problem persists.' d='Modules.Wpkcoloco.Accountverification'}</p>
        {elseif isset($welcome_voucher_given) && $welcome_voucher_given}
            <p>{l s='Congratulations! Your account has been verified.' d='Modules.Wpkcoloco.Accountverification'}</p>
        {/if}
    </div>
{/block}