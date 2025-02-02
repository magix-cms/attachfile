{strip}
    {if isset($data.id)}
        {$data = [$data]}
    {/if}
{/strip}
{if is_array($data) && !empty($data)}
    {foreach $data as $item}
        <a class="targetblank" href="{$url}/upload/attachfile/{$product.id}/{$item.name}.{$item.type}">
            Télécharger en {$item.type}
        </a>
    {/foreach}
{/if}