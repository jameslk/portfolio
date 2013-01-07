<table>
    <thead>
        <tr>
            <td>Test</td>
            <td>Result</td>
        </tr>
    </thead>
    
    <tbody>
        {foreach from=$tests item=test}
            <tr>
                <td>{$test.title}</td>
                
                <td>
                    {if $test.result == 'OK'}
                        Pass
                    {elseif $test.result == 'WARN'}
                        Warning
                    {else}
                        Failure
                    {/if}
                </td>
            </tr>
        {/foreach}
    </tbody>
</table>