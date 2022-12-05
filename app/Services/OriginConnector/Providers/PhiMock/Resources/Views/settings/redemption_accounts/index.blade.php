<h3>Phi Mock Gateway Redemption Account Settings</h3>
<div id="RedemptionContainer" test="testing!"></div>

@feature_available('global.redemption-accounts.points')
@section('scripts')
<script>
var RedemptionAccountOptions = Vue.extend({
    template: `
<div>No settings required for redemptions</div>
`,
    data: function () {
        return {!! json_encode($redemptionAccountOptions) !!}
    }
});
new RedemptionAccountOptions({el: '#RedemptionContainer'});
</script>
@endsection
@end_feature_available
