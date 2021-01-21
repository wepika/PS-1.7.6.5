<tbody>
<tr>
    <td>
        {$ticket.dttrs}
    </td>
    <td>
        {$ticket.ca}
    </td>
    <td>
        {$ticket.depense}
    </td>
    <td>
        {$ticket.idm}
    </td>

    <td>
        {if !is_null($ticket.receipt.jtkt)}
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ticket_{$ticket.idt}">
            {l s='More' d='Modules.Wpkcoloco.Ticket'}
        </button>
        {/if}
    </td>

</tr>
</tbody>

{if !is_null($ticket.receipt.jtkt)}
<!-- Modal -->
<div class="modal fade" id="ticket_{$ticket.idt}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle">{l s='Your ticket from' d='Modules.Wpkcoloco.Ticket'} {$ticket.dttrs} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="ticket_content_{$ticket.idt}" class="modal-body">
                <img class="logo img-fluid" src="{$shop.logo}">
                <br />
                <p class="text-center">{$ticket.receipt.jtkt nofilter}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{l s='Close' d='Modules.Wpkcoloco.Ticket'}</button>
                <button type="button" class="btn btn-primary print_button"  data-id_ticket="{$ticket.idt}">{l s='Print it !' d='Modules.Wpkcoloco.Ticket'}</button>
            </div>
        </div>
    </div>
</div>
{/if}