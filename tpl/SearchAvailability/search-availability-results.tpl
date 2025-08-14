{if $Openings|default:array()|count == 0}
    <div class="alert alert-warning shadow mt-3 text-center">
        <h4 class="alert-heading">
            <i class="bi bi-exclamation-triangle-fill"></i> {translate key=NoAvailableMatchingTimes}
        </h4>
    </div>
{else}
    <div class="card shadow">
        <div class="card-body py-4">
            <div class="row gy-3">
                {foreach from=$Openings item=opening}
                    <div class="col-sm-3">
                        <div class="opening card shadow-sm" data-resourceid="{$opening->Resource()->Id}"
                            data-startdate="{format_date date=$opening->Start() key=system_datetime}"
                            data-enddate="{format_date date=$opening->End() key=system_datetime}">
                            <h5 class="card-header resourceName" data-resourceId="{$opening->Resource()->Id}"
                                {if $opening->Resource()->HasColor()}
                                    style="background-color: {$opening->Resource()->Color};color:{$opening->Resource()->TextColor};"
                                {/if}>
                                <i class="bi bi-info-circle-fill pe-1"></i> {$opening->Resource()->Name}
                            </h5>
                            {assign var=key value=short_reservation_date}
                            {if $opening->SameDate()}
                                {assign var=key value=period_time}
                            {/if}
                            <div class="card-body dates px-2 py-1">
                                <h6 class="card-text">
                                    {format_date date=$opening->Start() key=res_popup} -
                                    {format_date date=$opening->End() key=$key}
                                </h6>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{/if}