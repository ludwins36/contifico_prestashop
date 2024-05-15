{if $status == 1 || $status == 3 }
    <span class="btn-group-action">
        <span class="btn-group">
            <a class="btn btn-default"
                href="{$link->getAdminLink('AdminContificoOrders', true, [], ['submitAction' => 'reloadDoc','id' => $id_order])|escape:'html':'UTF-8'}">
                <i class="icon-refresh"></i>
            </a>
        </span>
    </span>
{/if}