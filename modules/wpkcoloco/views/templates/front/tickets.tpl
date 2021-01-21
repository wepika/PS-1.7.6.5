{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Your tickets'  d='Modules.Wpkcoloco.Tickets'}
{/block}

{block name='page_content'}
    <table class="table">
        <thead>
        <th>{l s='Date' d='Modules.Wpkcoloco.Tickets'}</th>
        <th>{l s='Amount' d='Modules.Wpkcoloco.Tickets'}</th>
        <th>{l s='Points Earned' d='Modules.Wpkcoloco.Tickets'}</th>
        <th>{l s='Shop' d='Modules.Wpkcoloco.Tickets'}</th>
        <th>{l s='Ticket' d='Modules.Wpkcoloco.Tickets'}</th>
        </thead>
        {foreach $tickets as $ticket}
            {include file=$module_template_path|cat:'partials/ticket.tpl' ticket=$ticket}
        {/foreach}
    </table>
{/block}