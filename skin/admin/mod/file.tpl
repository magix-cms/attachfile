{*{if isset($scheme)}{$scheme|var_dump}{/if}*}
{include file="section/form/table-form-3.tpl" controller=$smarty.get.controller plugin='attachfile' data=$files idcolumn='id_paf' ajax_form=true activation=false search=false sortable=true edit=false}
