<?php

namespace App\Services;

use App\DTO\SearchProductsDTO;
use App\Services\ProductService;
use Illuminate\Http\Response;
use Illuminate\Contracts\Auth\Authenticatable;
class GoogleMerchantService
{
    private ProductService $productService;
    private array $categoryMapping = [
        '1' => 'Apparel &amp; Accessories&gt;Jewelry&gt;Earrings',
        '2' => 'Apparel &amp; Accessories&gt;Jewelry&gt;Necklaces',
        '3' => 'Apparel &amp; Accessories&gt;Jewelry&gt;Bracelets',
        '4' => 'Apparel &amp; Accessories&gt;Jewelry&gt;Rings',
        '9' => 'Apparel &amp; Accessories&gt;Jewelry&gt;Pendants &amp; Charms',
        '11' => 'Apparel &amp; Accessories&gt;Jewelry&gt;Watches',
    ];

    private array $materialMapping = [
        '1' => 'White Gold',
        '3' => 'Gold',
        '4' => 'Silver',
    ];

    private array $gemMapping = [
        '1' => 'Diamond',
        '2' => 'Emerald',
        '5' => 'Pearl',
    ];

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function generateGoogleMerchantFeed(): Response
    {
        $xmlHeader = $this->getXmlHeader();
        $xmlContent = $xmlHeader;

        // Process products in chunks to handle large datasets
        $page = 1;
        $perPage = 100000;

        do {
            $searchDTO = new SearchProductsDTO(perPage:1000000);

            $products = $this->productService->findMany($searchDTO);

            if ($products->count() > 0) {
                $formattedProducts = $this->formatProductsForGoogle($products->items());
                $chunkXml = $this->renderProductChunk($formattedProducts);
                $xmlContent .= $chunkXml;
            }

            $page++;
        } while ($products->hasMorePages());

        $xmlContent .= $this->getXmlFooter();

        return response($xmlContent, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function formatProductsForGoogle($products): array
    {
        $formattedProducts = [];

        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product->product_sku ?? $product->id,
                'title' => $this->sanitizeText($product->title),
                'description' => $this->formatDescription($product),
                'link' => $this->generateProductUrl($product),
                'image_links' => $product->image_urls ?? [],
                'condition' => $this->determineCondition($product),
                'availability' => $product->is_sold ? 'out of stock' : 'in stock',
                'price' => $this->formatPrice($product->price),
                'brand' => $this->getBrand($product),
                'category' => $this->getGoogleProductCategory($product->category),
                'material' => $this->getMaterialName($product->material),
                'gender' => $this->formatGender($product->gender),
                'weight' => $product->weight,
                'custom_labels' => $this->getCustomLabels($product),
                'city' => $product->city ?? 'თბილისი',
            ];
        }

        return $formattedProducts;
    }

    private function renderProductChunk(array $products): string
    {
        $xml = '';

        foreach ($products as $product) {
            $xml .= "<item>\n";
            $xml .= "    <g:id>" . htmlspecialchars($product['id'], ENT_XML1) . "</g:id>\n";
            $xml .= "    <g:title>" . htmlspecialchars($product['title'], ENT_XML1) . "</g:title>\n";
            $xml .= "    <g:description>" . htmlspecialchars($product['description'], ENT_XML1) . "</g:description>\n";
            $xml .= "    <g:link>" . htmlspecialchars($product['link'], ENT_XML1) . "</g:link>\n";

            // Handle multiple images
            if (!empty($product['image_links'])) {
                $xml .= "    <g:image_link>" . htmlspecialchars($product['image_links'][0], ENT_XML1) . "</g:image_link>\n";

                for ($i = 1; $i < count($product['image_links']) && $i < 10; $i++) {
                    $xml .= "    <g:additional_image_link>" . htmlspecialchars($product['image_links'][$i], ENT_XML1) . "</g:additional_image_link>\n";
                }
            }

            $xml .= "    <g:condition>" . $product['condition'] . "</g:condition>\n";
            $xml .= "    <g:availability>" . $product['availability'] . "</g:availability>\n";
            $xml .= "    <g:price>" . $product['price'] . "</g:price>\n";
            $xml .= "    <g:brand>" . htmlspecialchars($product['brand'], ENT_XML1) . "</g:brand>\n";
            $xml .= "    <g:google_product_category>" . $product['category'] . "</g:google_product_category>\n";

            // Additional jewelry-specific attributes
            if ($product['material']) {
                $xml .= "    <g:material>" . htmlspecialchars($product['material'], ENT_XML1) . "</g:material>\n";
            }

            if ($product['gender']) {
                $xml .= "    <g:gender>" . $product['gender'] . "</g:gender>\n";
            }

            if ($product['weight']) {
                $xml .= "    <g:custom_label_0>" . htmlspecialchars($product['weight'] . 'g', ENT_XML1) . "</g:custom_label_0>\n";
            }

            if ($product['city']) {
                $xml .= "    <g:custom_label_1>" . htmlspecialchars($product['city'], ENT_XML1) . "</g:custom_label_1>\n";
            }

            // Add custom labels
            foreach ($product['custom_labels'] as $index => $label) {
                if ($index < 5 && $label) { // Google allows up to 5 custom labels
                    $labelIndex = $index + 2; // Start from custom_label_2 since 0 and 1 are used
                    $xml .= "    <g:custom_label_{$labelIndex}>" . htmlspecialchars($label, ENT_XML1) . "</g:custom_label_{$labelIndex}>\n";
                }
            }

            $xml .= "</item>\n";
        }

        return $xml;
    }

    private function getXmlHeader(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
<channel>
<title>GeGold - Georgian Jewelry and Gold</title>
<link>https://gegold.ge</link>
<description>Premium jewelry, gold, and precious items from Georgia</description>

XML;
    }

    private function getXmlFooter(): string
    {
        return <<<XML
</channel>
</rss>
XML;
    }

    private function sanitizeText(string $text): string
    {
        // Remove emojis and clean text
        $text = preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', '', $text);
        $text = strip_tags($text);
        $text = trim($text);
        return $text ?: 'Georgian Jewelry Item';
    }

    private function formatDescription($product): string
    {
        $description = $this->sanitizeText($product->description ?? '');

        // Add material and weight info to description if available
        $additionalInfo = [];

        if ($product->material) {
            $materialName = $this->getMaterialName($product->material);
            if ($materialName) {
                $additionalInfo[] = "Material: {$materialName}";
            }
        }

        if ($product->weight) {
            $additionalInfo[] = "Weight: {$product->weight}g";
        }

        if ($product->stamp) {
            $additionalInfo[] = "Stamp: {$product->stamp}";
        }

        if (!empty($additionalInfo)) {
            $description .= ' | ' . implode(' | ', $additionalInfo);
        }

        return $description ?: 'Premium jewelry item from Georgia';
    }

    private function generateProductUrl($product): string
    {
        $baseUrl = config('app.url', 'https://gegold.ge');
        return $baseUrl . '/product-details/' . ($product->slug ?? $product->id) . '/';
    }

    private function determineCondition($product): string
    {
        // Based on stamp or other indicators
        if (isset($product->stamp) && in_array($product->stamp, ['3', '4', '6'])) {
            return 'used'; // Vintage/antique items
        }
        return 'new';
    }

    private function formatPrice($price): string
    {
        return number_format((float)$price, 2, '.', '') . ' GEL';
    }

    private function getBrand($product): string
    {
        // Try to extract brand from creator or use default
        if (isset($product->creator['name'])) {
            return $product->creator['name'];
        }

        if (isset($product->creator['first_name'], $product->creator['last_name'])) {
            return $product->creator['first_name'] . ' ' . $product->creator['last_name'];
        }

        return 'GeGold';
    }

    private function getGoogleProductCategory(string $categoryId): string
    {
        return $this->categoryMapping[$categoryId] ?? 'Apparel &amp; Accessories&gt;Jewelry';
    }

    private function getMaterialName(string $materialId): ?string
    {
        return $this->materialMapping[$materialId] ?? null;
    }

    private function formatGender(?string $gender): ?string
    {
        if (!$gender) return null;

        return match($gender) {
            'male' => 'male',
            'female' => 'female',
            default => 'unisex'
        };
    }

    private function getCustomLabels($product): array
    {
        $labels = [];

        // Add gem information
        if ($product->gem) {
            if (is_array($product->gem)) {
                $gemNames = array_map(fn($gemId) => $this->gemMapping[$gemId] ?? "Gem {$gemId}", $product->gem);
                $labels[] = implode(', ', $gemNames);
            } else {
                $labels[] = $this->gemMapping[$product->gem] ?? "Gem {$product->gem}";
            }
        }

        // Add customization availability
        if (isset($product->customization['available']) && $product->customization['available']) {
            $labels[] = 'Customizable';
        }

        // Add paid advertisement flag
        if ($product->is_paid_adv) {
            $labels[] = 'Featured';
        }

        return $labels;
    }

    public function index()
    {
        return $this->generateGoogleMerchantFeed();
    }
}
