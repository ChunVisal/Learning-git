<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Products</title>
    @vite(['resources/js/app.js'])
</head>

<body x-data="productForm()">
    <h1>Products</h1>

    <a href="{{ route('products.stock-movements') }}">View All Stock Movements</a>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <p x-show="loading">Loading...</p>

    <div class="flex">
        @include('partials.search-input', ['placeholder' => 'Search product name'])
        <select x-model="priceFilter">
            <option value="">All Prices</option>
            <option value="low">Under $50</option>
            <option value="mid">$50 - $200</option>
            <option value="high">Over $200</option>
        </select>
        <button @click="open = !open">
            Add product
        </button>
    </div>

    <div x-show="open" x-transition>
        <form @submit.prevent="submitForm">
            @csrf

            <select x-model="form.category">
                <option value="">-- Select existing category --</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.name" x-text="cat.name"></option>
                </template>
            </select>

            <span>or</span>

            <input type="text" x-model="form.category" placeholder="Type new category">

            <input type="text" name="name" x-model="form.name" placeholder="Product name">
            <input type="number" name="price" x-model="form.price" placeholder="Price" step="0.01">
            <input type="number" name="stock" x-model="form.stock" placeholder="Stock">

            <button type="submit" x-text="editingId ? 'Update Product' : 'Add Product'">
            </button>
            <button type="button" @click="closeForm()">✕</button>
        </form>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>Name</th>
                <th>categories</th>
                <th>Price ($)</th>
                <th>Stock</th>
                <th>date</th>
                <th>action</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="product in filteredProducts()" :key="product.id">
                <tr>
                    <td x-text="product.name"></td>
                    <td x-text="product.category?.name"></td>
                    <td x-text="product.price"></td>
                    <td x-text="product.stock"></td>
                    <td>
                        <span x-text="'Created: ' + new Date(product.created_at).toLocaleDateString()"></span>
                        <span x-text="' | Updated: ' + new Date(product.updated_at).toLocaleDateString()"></span>
                    </td>
                    <th><button @click="updateProduct(product)">Edit</button>
                        <button @click="deleteProduct(product.id)">Delete</button>
                    </th>
                </tr>
            </template>
        </tbody>
    </table>

    <p x-show="products.length === 0">No Product yet</p>

    <p x-show="products.length > 0 && filteredProducts().length === 0">
        No results found. Try adjusting your search or filter.
    </p>
</body>

</html>

<script>
    function productForm() {
        return {
            open: false,
            loading: false,

            // search
            searchQuery: '',
            priceFilter: '',

            products: [],
            categories: [],
            form: {
                name: '',
                price: '',
                stock: '',
                category: ''
            },
            editingId: null,

            init() {
                this.loading = true;

                fetch('/products', {
                        headers: apiHeaders()
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.products = data;
                        this.loading = false;
                    });

                fetch('/categories', {
                        headers: apiHeaders()
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.categories = data;
                    });
            },

            closeForm() {
                this.open = false;
                this.editingId = null;
                this.form = {
                    name: '',
                    price: '',
                    stock: '',
                    category: ''
                };
            },

            filteredProducts() {
                return this.products
                    .filter(p =>
                        p.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                    )
                    .filter(p => {
                        if (this.priceFilter === 'low') return p.price < 50;
                        if (this.priceFilter === 'mid') return p.price >= 50 && p.price <= 200;
                        if (this.priceFilter === 'high') return p.price > 200;
                        return true;
                    });
            },

            updateProduct(product) {
                this.editingId = product.id;
                this.form = {
                    name: product.name,
                    price: product.price,
                    stock: product.stock
                };
                this.form.category = product.category?.name ?? '';
                this.open = true;
            },

            deleteProduct(id) {
                if (!confirm("are you sure to delete this produdct?")) return;

                fetch(`/products/${id}`, {
                        method: 'DELETE',
                    })
                    .then(async (res) => {
                        const data = await res.json();

                        if (!res.ok) {
                            alert(data.message);
                            return;
                        }

                        this.init();

                        this.products = this.products.filter(p => p.id !== id);
                    })
                    .catch(() => {
                        alert('Error deleting product.');
                    });
            },

            submitForm() {
                if (!this.form.name || !this.form.price || !this.form.stock || !this.form.category) {
                    alert('All fields are required!');
                    return;
                }

                const isDuplicate = this.products.some(p =>
                    p.name.toLowerCase() === this.form.name.toLowerCase() && p.id !== this.editingId
                );

                if (isDuplicate) {
                    alert(`"${this.form.name}" already exists!`);
                    return; // ← stops here, form stays open, inputs stay untouched
                }

                const isEditing = this.editingId !== null;
                const url = isEditing ? `/products/${this.editingId}` : '/products';
                const method = isEditing ? 'PUT' : 'POST';

                fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(() => {
                        this.init();
                        this.closeForm();
                    })
                    .catch(err => {
                        alert('Error submitting data. Check backend layout logs.');
                    });
            }
        };
    }
</script>
