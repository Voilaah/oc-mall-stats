<?php

namespace Voilaah\MallStats\Models;

use DB;
use Exception;
use System\Models\File;
use ApplicationException;
use OFFLINE\Mall\Models\Product;
use Backend\Models\ImportModel;
use System\Classes\MediaLibrary;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Classes\Index\Noop;
use OFFLINE\Mall\Classes\Index\Index;
use Illuminate\Support\Facades\Artisan;
use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use Voilaah\MallStats\Classes\Import\ProductImportContainer;


/**
 * ProductImport Model
 */
class ProductImport extends ImportModel
{
    /**
     * Validation rules
     */
    public $rules = [
        'name'   => 'required',
        'price' => 'required'
    ];

    protected $appends = [];

    protected $categoryNameCache = [];

    public $productImageStoragePath = '/import/products_images';

    public $imagePublic = true;

    public function importData($results, $sessionKey = null)
    {

        $firstRow = reset($results);

        /*
         * Validation
         */
        if ($this->auto_create_categories && !array_key_exists('categories', $firstRow)) {
            throw new ApplicationException('Please specify a match for the Categories column.');
        }

        // Use a Noop-Indexer so no unnecessary queries are run during seeding.
        // the index will be re-built once everything is done.
        $originalIndex = app(Index::class);
        app()->bind(Index::class, function () {
            return new Noop();
        });

        if ($this->cleanup_existing) {
            $this->cleanup();
        }

        foreach ($results as $row => $data) {

            try {

                // try to find images in the Media Library
                // $imgName = $data['image'] ? $data['image'] : $data['name'];
                // $imgName = trim($imgName);
                // trace_log('searching image ' . $imgName);
                // $image = $this->findImage($this->productImageStoragePath, $imgName);
                // if (!$image) {
                //     $msg = 'We could not found the following image name in the media library:' .  $this->productImageStoragePath . '/' . $imgName . '.jpg';
                //     $this->logWarning($row, $msg);
                // }
                $allow_out_of_stock_purchases = array_get($data, 'allow_out_of_stock_purchases', null);
                $allow_out_of_stock_purchases = $allow_out_of_stock_purchases == 1 ? true : false;
                $product = new ProductImportContainer([
                    'id'                            => $data['id'],
                    'user_defined_id'               => $data['user_defined_id'],
                    'name'                          => $data['name'],
                    'slug'                          => $data['slug'],
                    'auto_published'                => $this->auto_publish_products,
                    'description_short'             => $data['description_short'],
                    'description'                   => $data['description'],
                    'price'                         => $this->cleanupData($data['price']),
                    'weight'                        => $this->cleanupData($data['weight']),
                    'height'                        => $this->cleanupData($data['height']),
                    'width'                         => $this->cleanupData($data['width']),
                    'length'                        => $this->cleanupData($data['length']),
                    'stock'                         => $data['stock'],
                    'allow_out_of_stock_purchases'  => $allow_out_of_stock_purchases ?? false,
                    'categories'                    => $this->categories ? $this->getCategorySlugForProduct() : $this->getCategorySlugForProduct($data),
                    // 'imagePath'                     => $image,
                    // 'addImagePath'                  => $addImage,
                ]);

                $product->create();

                /*
                 * Log results
                 */
                $this->logCreated();


            } catch (\Exception $ex) {
                trace_log($ex);
                $this->logError($row, $ex->getMessage());
            }
        }

        app()->bind(Index::class, function () use ($originalIndex) {
            return $originalIndex;
        });

        Artisan::call('mall:reindex', ['--force' => true]);
    }


    protected function cleanup()
    {
        Artisan::call('cache:clear');

        // DB::table('system_files')
        // ->where('attachment_type', 'LIKE', 'OFFLINE%Mall%')
        // ->orWhere('attachment_type', 'LIKE', 'mall.%')
        // ->delete();
        if ($this->categories) {
            foreach ($this->categories as $key => $selectedCategory) {
                $category = Category::with(['products' => function ($q) {
                        $q->where('published', true)->orderBy('name', 'asc');
                    }])->find($this->selectedCategory);
                $category->products->each(function (Product $product) {
                    $product->delete();
                });
            }
        } else {
            // cleanup all existing products
            $products = Product::get();
            $products->each(function (Product $product) {
                $product->delete();
            });
        }

        $index = app(Index::class);
        $index->drop(ProductEntry::INDEX);
        $index->drop(VariantEntry::INDEX);

        if ($this->update_existing) {
            //
        }
    }


    private function cleanupData($number)
    {
        return str_replace(['g', 'gm', '$', 'kcal', 'cal'], ['','','',''], $number);
    }


    private function getCategorySlugForProduct($data = null)
    {
        $ids = [];

        if ($this->auto_create_categories) {
            $categoryNames = $this->decodeArrayValue(array_get($data, 'categories'));
            foreach ($categoryNames as $name) {
                if (!$name = trim($name)) {
                    continue;
                }

                if (isset($this->categoryNameCache[$name])) {
                    $ids[] = $this->categoryNameCache[$name];
                }
                else {

                    $newCategory = Category::firstOrCreate(['name' => $name, 'slug' => str_slug($name)]);
                    $ids[] = $this->categoryNameCache[$name] = $newCategory->id;
                }
            }
        }
        elseif ($this->categories) {
            foreach ($this->categories as $id) {
                $ids[] = $id;
                // if (isset($this->categoryNameCache[$id])) {
                //     $ids[] = $this->categoryNameCache[$id];
                // }
                // else {
                //     $category = Category::find($id);
                //     $ids[] = $this->categoryNameCache[$id] = $category->code;
                // }
            }
            // $ids = (array) $this->categories;
        }
        return $ids;
    }

    private function findCategories($category)
    {
        $dictionnary = [
                    'ready-to-eat-meals'    => 'ready-to-eat-meals',
                    'shakes'                => 'shakes',
                    'snacks'                => 'snacks',
                    'flexible-monthly-plan' => 'flexible-monthly-plan',
                    'a-la-carte'            => 'a-la-carte',
                    'supplements'            => 'supplements',
                ];

        return $dictionnary[$category];
    }


    private function findImage($path, $imagename)
    {
        $library = MediaLibrary::instance();
        $files = $library->listFolderContents($path, 'title', 'image');

        foreach ($files as $file) {
            $pathinfo = pathinfo($file->publicUrl);
            // trace_log('checking image ' . urldecode($pathinfo['filename']));

            if (
                urldecode($pathinfo['filename']) == $imagename
                || str_slug(urldecode($pathinfo['filename'])) == str_slug($imagename)
            ) {
                return storage_path('app/media' . $file->path);
                // $newFile = new File();
                // $newFile->is_public = $this->imagePublic;
                // $newFile->fromFile(storage_path('app/media' . $file->path));

                // return $newFile;
            }
        }
    }

    public function getCategoriesOptions()
    {
        return Category::lists('name', 'id');
    }
}
