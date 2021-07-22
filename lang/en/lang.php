<?php return [
    'plugin' => [
        'name' => 'MallStats',
        'description' => 'Provide some basics statistics for October CMS MALL Plugin'
    ],
    'menu' => [
        'statistics' => 'Statistics',
        'exports' => 'Available Exports',
        'export' => 'Exports',
        'imports' => 'Available Imports',
        'import' => 'Imports',
    ],
    'import' => [
        'import_customers' => "Import Customers",
        'import_products' => "Import Products",
        'import_products_cleanup_options' => "Cleanup existing products?",
        'import_products_cleanup_options_comment' => "If checked, the cleanup process will cleanup all products attached to the categories you selected (if any) or all existing products.",

        'import_products_update_options2_comment' => "The import does not update existing products, it will always create new product.",
        'import_products_update_options_comment' => "The default Inventory management method is set to Article.",
        'import_products_update_options_comment' => "The default Inventory management method is set to Article.",
        'import_products_image_path_comment' => "The image path is relative to the media library path. (Ex: /my_import/bike-cruiser-500.jpg)",
        'import_products_price_comment' => "The default currency is used to set the product price.",
        'import_products_short_description_comment' => "The short description is used in the SEO meta-description field.",
        'import_products_stock_comment' => "The default 'stock' value is set to 1.",
        'import_products_default_quantity_comment' => "The default 'default quantity for purchase' is set to 1.",
        'import_products_sample_comment' => "There is a sample file 'demo-products.csv' for products import in the folder 'demo' at the root of the plugin.",
        'import_products_description_comment' => "The description value should be HTML (Ex: &lt;p&gt;My description&lt;/p&gt;)",

        'import_products_allow_out_of_stock_purchases_comment' => "The default 'allow out of stock purchases' value is set to false.",
        'import_products_update_existing_comment' => 'Check this box to update products that have exactly the same ID, title or slug.',
        'import_products_auto_published_label' => 'Automatically publish the imported products.',
        'import_products_auto_create_categories_label' => 'Create categories specified in the import file.',
        'import_products_auto_create_categories_comment' => 'You should match the Categories column to use this feature, otherwise select the default categories to use from the items below.',
        'import_products_categories_label' => 'Categories',
        'import_products_categories_comment' => 'Select the categories that imported products will belong to (optional).',
        'import_products_labels' => [
            'id' => 'ID',
            'user_defined_id' => 'User defined ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'short_description' => 'Short description',
            'description' => 'Description',
            'stock' => 'Stock',
            'price' => 'Price',
            'allow_out_of_stock_purchases' => 'Allow out of stock purchases?',
            'weight' => 'Weight',
            'height' => 'Height',
            'width' => 'Width',
            'length' => 'Length',
            'image' => 'Image path',
            'category' => 'Categories',
            'is_virtual' => 'Is virtual?',
        ],
    ],
    'export' => [
        'export_products' => "Export Products",
        'export_customers' => "Export Customers",
        'export_orders' => "Export Orders",
        'export_sales' => "Export Sales",
        'customer_id' => "# Customer",
        'product_id' => "# Product",
        'product_name' => "Product name",
        'product_categories' => "Product categories",
        'quantity' => "Quantity",
        'total' => "Total",
        'items' => "Item(s) [id-name-quantity]",
        'shipping' => "Shipping",
        'discounts_applied' => "Discount(s) applied [Discount name: Discount value]",
        'total_discounts' => "Total discount",
    ],
    'titles' => [
        'best_sellers' => 'Best sellers',
        'low_sellers' => 'Low sellers',
        'sales_period_year' => 'Sales by year',
        'sales_current_month' => 'Sales for this month',
        'sales_period_month' => 'Sales by month (for :year)',
        'sales_average' => 'Average Sales',
        'orders_current_month' => 'Orders for this month',
        'orders_period_month' => 'Orders by month (for :year)',
        'orders_average' => 'Average Orders',
        'nb_customers' => 'Total payee / Total customers',
        'nb_customers_comment' => 'Customers who made at least one purchase',
        'nb_unique_customers_per_month' => '(Unique) Customers by month (for :year)',
    ],
];
