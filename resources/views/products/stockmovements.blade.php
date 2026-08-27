<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock movement</title>
    @vite(['resources/js/app.js'])
</head>

<body x-data="movementPage()">
    <a href="{{ route('products') }}">Back to Products</a>

    <h3>stockmovement</h3>
    @include('partials.search-input', ['placeholder' => 'Search by product'])
    <p x-show="loading">...loading</p>

    <table border="1">
        <thead>
            <tr>
                <th>name</th>
                <th>Type</th>
                <th>Qty</th>
                <th>reason</th>
                <th>Balance After</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="m in filteredProducts()" :key="m.id">
                <tr>
                    <td x-text="m.product.name"></td>
                    <td x-text="m.type"></td>
                    <td x-text="m.quantity"></td>
                    <td x-text="m.reason"></td>
                    <td x-text="m.balance_after"></td>
                    <td x-text="new Date(m.created_at).toLocaleString()"></td>
                </tr>
            </template>
        </tbody>
    </table>
    <p x-show="movements.length === 0">No Product movementyet yet</p>
    <p x-show="movements.length > 0 && filteredProducts().length === 0">
        No results found. Try adjusting your search or filter.
    </p>
</body>

</html>
<script>
    function movementPage() {
        return {
            searchQuery: '',
            loading: false,
            movements: [],

            filteredProducts() {
                return this.movements
                    .filter(m =>
                        m.product.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
            },

            init() {
                this.loading = true;
                fetch('/products/stock-movements', {
                        headers: apiHeaders()
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.movements = data;
                        this.loading = false;
                    });
            }
        }
    }
</script>
