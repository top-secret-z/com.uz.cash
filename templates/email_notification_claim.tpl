{if $mimeType === 'text/plain'}
{lang}cash.claim.notification.mail.plaintext{/lang}

{@$event->getUserNotificationObject()->getMailText($mimeType)} {* this line ends with a space *}
{else}
    {lang}cash.claim.notification.mail.html{/lang}
    {assign var='claim' value=$event->getUserNotificationObject()}

    {capture assign='fileContent'}
    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="boxContent">
                <div>
                    {@$claim->getMailText($mimeType)}
                </div>
            </td>
        </tr>
    </table>
    {/capture}
    {include file='email_paddingHelper' block=true class='box' content=$fileContent sandbox=true}
{/if}
