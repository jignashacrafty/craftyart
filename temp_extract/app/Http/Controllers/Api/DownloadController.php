<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AesCipher;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Category;
use App\Models\NewCategory;
use App\Models\Design;
use App\Models\SpecialKeyword;
use App\Models\SpecialPage;
use App\Models\Font;
use App\Models\FontList;
use App\Models\FontFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use FontLib\Font as FontLib;

class DownloadController extends Controller
{

    private static $allLandingPages = [
        ['name' => 'Invitation', 'path' => 'invitation'],
        ['name' => 'Marketing', 'path' => 'marketing'],
        ['name' => 'Business', 'path' => 'business'],
        ['name' => 'Quotes', 'path' => 'quotes'],
        ['name' => 'Resume', 'path' => 'resume'],
        ['name' => 'Flyer', 'path' => 'flyer'],
        ['name' => 'Calendar', 'path' => 'calendar'],
        ['name' => 'Bridal Shower', 'path' => 'bridal-shower'],
        ['name' => 'Logo', 'path' => 'logos'],
        ['name' => 'Wedding', 'path' => 'wedding'],
        ['name' => 'Birthday', 'path' => 'birthday-invitation'],
        ['name' => 'Baby Shower', 'path' => 'baby-shower-invitation'],
        ['name' => 'Party', 'path' => 'party-invitation'],
        ['name' => 'Brochure', 'path' => 'brochure-design'],
        ['name' => 'Poster', 'path' => 'poster'],
        ['name' => 'Indian', 'path' => 'indian'],
        ['name' => 'Baptism', 'path' => 'baptism'],
        ['name' => 'Gujarati', 'path' => 'gujarati'],
        ['name' => 'English', 'path' => 'english'],
        ['name' => 'Hindu', 'path' => 'hindu'],
        ['name' => 'Muslim', 'path' => 'muslim'],
        ['name' => 'Punjabi', 'path' => 'punjabi'],
        ['name' => 'Christian', 'path' => 'christian'],
        ['name' => 'Tamil', 'path' => 'tamil'],
        ['name' => 'Pregnancy Announcement', 'path' => 'pregnancy-announcement'],
        ['name' => 'Birth Announcement', 'path' => 'birth-announcement'],
        ['name' => 'Christening', 'path' => 'christening'],
        ['name' => 'Engagement', 'path' => 'engagement'],
        ['name' => 'Engagement Party', 'path' => 'engagement-party'],
        ['name' => 'Party Invitation', 'path' => 'party-invitation'],
        ['name' => 'Haldi Ceremony', 'path' => 'haldi-ceremony'],
        ['name' => 'Rehearsal Dinner', 'path' => 'rehearsal-dinner'],
        ['name' => 'Save The Date', 'path' => 'save-the-date'],
        ['name' => 'South Indian', 'path' => 'south-indian'],
        ['name' => 'Biodata', 'path' => 'biodata'],
        ['name' => 'Cards', 'path' => 'cards'],
    ];

    private static $allToolsPages = [
        ['name' => 'Background Remover', 'path' => 'background-remover'],
        ['name' => 'Brand kit', 'path' => 'brand-kit'],
        ['name' => 'Resize', 'path' => 'resize'],
        ['name' => 'Caricature', 'path' => 'caricature'],
        ['name' => 'Style kit', 'path' => 'style-kit'],
        ['name' => 'Customize Invitation', 'path' => 'customize-invitation'],
        ['name' => 'Qr Code Generator', 'path' => 'qr-code-generator'],
        // ['name' => 'Image Compressor', 'path' => 'tools/image-compressor'],
        // ['name' => 'Image Enhancer', 'path' => 'tools/image-enhancer'],
        // ['name' => 'Image Resizer', 'path' => 'tools/image-resizer'],
        // ['name' => 'Jpg to Pdf', 'path' => 'tools/jpg-to-pdf'],
        // ['name' => 'Pdf Editor', 'path' => 'tools/pdf-editor'],
        // ['name' => 'Png to Jpg', 'path' => 'tools/png-to-jpg'],
        // ['name' => 'Pdf to Jpg', 'path' => 'tools/pdf-to-jpg'],
        // ['name' => 'Pdf Merger', 'path' => 'tools/pdf-merger'],
        // ['name' => 'Reverse Image', 'path' => 'tools/reverse-image'],
    ];


    function keywords(Request $request)
    {

        // if ($request->has('edsfds')) {
        //     $fontFamilies = FontFamily::all();

        //     $fontFamilyRows = array();
        //     foreach ($fontFamilies as $fontFamily) {

        //         $fontLists = FontList::where("fontFamilyId", $fontFamily->id)->where("status", 1)->get();

        //         if ($fontLists->count() != 0) {
        //             $fontListRows = array();

        //             $support_bold = 0;
        //             $support_italic = 0;

        //             foreach ($fontLists as $font) {

        //                 if ($font->support_bold == 1) {
        //                     $support_bold = 1;
        //                 }

        //                 if ($font->support_italic == 1) {
        //                     $support_italic = 1;
        //                 }

        //                 $fontListRows[] = array(
        //                     'familyId' => $font->fontFamilyId,
        //                     'fontName' => $font->fontName,
        //                     'fontType' => $font->fontType,
        //                     'fontUrl' => $font->fontUrl,
        //                     'fontWeight' => $font->fontWeight,
        //                     'supportBold' => $font->support_bold == 1 ? true : false,
        //                     'supportItalic' => $font->support_italic == 1 ? true : false,
        //                 );
        //             }

        //             $fontFamily->support_bold = $support_bold;
        //             $fontFamily->support_italic = $support_italic;

        //             $fontFamily->save();

        //             $fontFamilyRows[] = array(
        //                 'familyId' => $fontFamily->id,
        //                 'fontFamily' => $fontFamily->fontFamily,
        //                 'fontThumb' => $fontFamily->fontThumb,
        //                 'uniname' => $fontFamily->uniname,
        //                 'supportType' => $fontFamily->supportType,
        //                 'isPremium' => $fontFamily->is_premium,
        //                 'fontList' => $fontListRows
        //             );
        //         }
        //     }

        //     return $fontFamilyRows;
        // }

        $domainName = "www.craftyartapp.com/";

        $specialData = SpecialKeyword::select('title', 'name')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $specialPages = SpecialPage::select('title', 'page_slug')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $catData = Category::select('id', 'id_name', 'category_name')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $newCats = NewCategory::select('id', 'id_name', 'parent_category_id', 'category_name')->where("status", 1)->orderBy('created_at', 'DESC')->get();

        $linkArray = array();
        $kPages = array();
        $sPages = array();
        $oldCatDatas = array();
        $catDatas = array();
        $lPages = array();
        $tPages = array();

        foreach ($specialData as $temp) {
            $kPages[] = array(
                "title" => $temp->title,
                "link" => "https://" . $domainName . "k/" . $temp->name
            );
        }

        foreach ($specialPages as $page) {
            $sPages[] = array(
                "title" => $page->title,
                "link" => "https://" . $domainName . $page->page_slug
            );
        }

        foreach ($catData as $cat) {
            if (Design::where('category_id', $cat->id)->where("status", 1)->exists()) {
                $oldCatDatas[] = array(
                    "title" => $cat->category_name,
                    "link" => "https://" . $domainName . "templates/" . $cat->id_name
                );
            }
        }

        foreach ($newCats as $newCat) {
            if (Design::where('new_category_id', $newCat->id)->where("status", 1)->exists()) {
                $catDatas[] = $this->getParentCat($domainName, $newCat);
            }
        }

        foreach (self::$allLandingPages as $page) {
            $lPages[] = array(
                "title" => $page['name'],
                "link" => "https://" . $domainName . $page['path']
            );
        }

        foreach (self::$allToolsPages as $page) {
            $tPages[] = array(
                "title" => $page['name'],
                "link" => "https://" . $domainName . $page['path']
            );
        }

        $res['success'] = true;
        $res['kPages'] = $kPages;
        $res['sPages'] = $sPages;
        $res['oldCatDatas'] = $oldCatDatas;
        $res['catDatas'] = $catDatas;
        $res['lPages'] = $lPages;
        $res['tPages'] = $tPages;

        return $res;
    }



    private function getParentCat($domainName, $newCat)
    {
        $idName = $newCat->id_name;
        if ($newCat->parent_category_id != 0) {
            $res = NewCategory::select('id', 'id_name', 'parent_category_id', 'category_name')->where("id", $newCat->parent_category_id)->where("status", 1)->first();
            if ($res) {
                $idName = $res->id_name . '/' . $newCat->id_name;
            }
        }

        return array(
            "title" => $newCat->category_name,
            "link" => "https://" . $domainName . "templates/" . $idName
        );
    }

    function sitemap(Request $request)
    {

        $domainName = "www.craftyartapp.com/";
        $mediaUrl = "https://media.craftyartapp.com/";

        if ($request->has('domainName')) {
            $domainName = $request->input('domainName') . "/";
        }

        $pages = [
            "https://" . $domainName . "invitation",
            "https://" . $domainName . "logos",
            "https://" . $domainName . "marketing",
            "https://" . $domainName . "business",
            "https://" . $domainName . "background-remover",
            "https://" . $domainName . "brand-kit",
            "https://" . $domainName . "resize",
            "https://" . $domainName . "caricature",
            "https://" . $domainName . "style-kit",
            "https://" . $domainName . "customize-invitation",
            "https://" . $domainName . "wedding",
            "https://" . $domainName . "birthday-invitation",
            "https://" . $domainName . "baby-shower-invitation",
            "https://" . $domainName . "party-invitation",
            "https://" . $domainName . "brochure-design",
            "https://" . $domainName . "flyer",
            "https://" . $domainName . "business-card",
            "https://" . $domainName . "poster",
            "https://" . $domainName . "resume",
            "https://" . $domainName . "plans",
            "https://" . $domainName . "about-us",
            "https://" . $domainName . "plans",
            "https://" . $domainName . "contact-us",
            "https://" . $domainName . "blog/",
            "https://" . $domainName . "privacy-policy",
            "https://" . $domainName . "referral-program",
            "https://" . $domainName . "term-condition",
            "https://" . $domainName . "copyright-information",
            "https://" . $domainName . "indian",
            "https://" . $domainName . "tools/qr-code-generator",
            "https://" . $domainName . "cards",
            "https://" . $domainName . "biodata",
            "https://" . $domainName . "south-indian",
            "https://" . $domainName . "save-the-date",
            "https://" . $domainName . "rehearsal-dinner",
            "https://" . $domainName . "haldi-ceremony",
            "https://" . $domainName . "engagement-party",
            "https://" . $domainName . "engagement",
            "https://" . $domainName . "christening",
            "https://" . $domainName . "birth-announcement",
            "https://" . $domainName . "pregnancy-announcement",
            "https://" . $domainName . "christian",
            "https://" . $domainName . "tamil",
            "https://" . $domainName . "punjabi",
            "https://" . $domainName . "muslim",
            "https://" . $domainName . "hindu",
            "https://" . $domainName . "english",
            "https://" . $domainName . "gujarati",
            "https://" . $domainName . "baptism",
            "https://" . $domainName . "tools/image-compressor",
            "https://" . $domainName . "tools/image-enhancer",
            "https://" . $domainName . "tools/image-resizer",
            "https://" . $domainName . "tools/jpg-to-pdf",
            "https://" . $domainName . "tools/pdf-editor",
            "https://" . $domainName . "tools/png-to-jpg",
            "https://" . $domainName . "tools/pdf-to-jpg",
            "https://" . $domainName . "tools/pdf-merger",
            "https://" . $domainName . "tools/reverse-image",
        ];

        $specialPages = SpecialPage::select('page_slug')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $catData = Category::select('id', 'id_name')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $newCats = NewCategory::select('id', 'id_name', 'parent_category_id', 'category_name')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $tempData = Design::select('id_name', 'thumb_array')->where("status", 1)->orderBy('created_at', 'DESC')->get();
        $specialData = SpecialKeyword::select('name')->where("status", 1)->orderBy('created_at', 'DESC')->get();

        $linkArray = array();

        foreach ($specialPages as $page) {
            $linkArray[] = array(
                "id_name" => "https://" . $domainName . $page->page_slug
            );
        }

        foreach ($catData as $cat) {
            if (Design::where('category_id', $cat->id)->where("status", 1)->exists()) {
                $linkArray[] = array(
                    "id_name" => "https://" . $domainName . "templates/" . $cat->id_name
                );
            }
        }

        foreach ($newCats as $newCat) {
            if (Design::where('new_category_id', $newCat->id)->where("status", 1)->exists()) {
                $linkArray[] = array(
                    "id_name" => $this->getParentCat($domainName, $newCat)["link"]
                );
            }
        }

        // foreach ($tempData as $temp) {
        //     $linkArray[] = array(
        //         "id_name" => "https://" . $domainName . "templates/p/".$temp->id_name
        //     );
        // }

        foreach ($specialData as $temp) {
            $linkArray[] = array(
                "id_name" => "https://" . $domainName . "k/" . $temp->name
            );
        }

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

        foreach (self::$allLandingPages as $page) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . $page['path']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach (self::$allToolsPages as $page) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . $page['path']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach ($linkArray as $url) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars($url['id_name']) . '</loc>' . PHP_EOL;
            $sitemap .= '</url>' . PHP_EOL;
        }

        foreach ($tempData as $temp) {
            $sitemap .= '<url>' . PHP_EOL;
            $sitemap .= '<loc>' . htmlspecialchars("https://" . $domainName . "templates/p/" . $temp->id_name) . '</loc>' .
                PHP_EOL;

            $thumbArray = json_decode($temp->thumb_array);
            if (is_array($thumbArray)) {
                foreach ($thumbArray as $thumb) {
                    $sitemap .= '<image:image>' . PHP_EOL;
                    $sitemap .= '<image:loc>' . htmlspecialchars($mediaUrl . $thumb) . '</image:loc>' . PHP_EOL;
                    $sitemap .= '</image:image>' . PHP_EOL;
                }
            }

            $sitemap .= '</url>' . PHP_EOL;
        }

        $sitemap .= '</urlset>';

        return $sitemap;
    }

    function catalog(Request $request)
    {

        $country = $request->input("country");
        if (!$country) {
            $country = "US";
        }

        $tempData = Design::where("status", 1)->orderBy('created_at', 'DESC')->get();

        $linkArray = [];

        $sitemap = '
<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL;

        $sitemap .= '<channel>' . PHP_EOL;
        $sitemap .= '<title>CraftyArt</title>' . PHP_EOL;
        $sitemap .= '
    <link>https://www.craftyartapp.com/templates</link>' . PHP_EOL;
        $sitemap .= '<description>craftyart premium templates</description>' . PHP_EOL;

        $isIndia = strtoupper($country) == "IN";

        $fixAmount = $isIndia ? HelperController::$INR_AMOUNT : HelperController::$USD_AMOUNT;

        foreach ($tempData as $item) {

            if ($item->description) {
                $link = "https://www.craftyartapp.com/templates/p/" . $item->id_name;
                $image_link = "https://panel.craftyartapp.com/templates/" . $item->post_thumb;

                $size = $item->total_pages;
                if ($size > 1) {
                    $extraPage = $size - 1;
                    $fixAmount = $isIndia ? HelperController::$INR_AMOUNT : HelperController::$USD_AMOUNT;
                    $perPageRate = $isIndia ? HelperController::$INR_EXTRA_PAGE_AMOUNT : HelperController::$USD_EXTRA_PAGE_AMOUNT;
                    $amount = $fixAmount + ($perPageRate * $extraPage);
                } else {
                    $amount = $fixAmount;
                }

                $maxAmount = $isIndia ? HelperController::$INR_MAX_AMOUNT : HelperController::$USD_MAX_AMOUNT;

                if ($amount > $maxAmount) {
                    $amount = $maxAmount;
                }

                $currency = $isIndia ? 'INR' : 'USD';

                $finalAmount = strval($amount) . ' ' . $currency;

                if ($item->is_premium == 1) {
                    $finalAmount = strval(0.0) . ' ' . $currency;
                }

                $sitemap .= '<item>' . PHP_EOL;
                $sitemap .= '<g:brand>' . 'CraftyArt' . '</g:brand>' . PHP_EOL;
                $sitemap .= '<g:id>' . htmlspecialchars($item->id) . '</g:id>' . PHP_EOL;
                $sitemap .= '<g:title>' . htmlspecialchars($item->post_name) . '</g:title>' . PHP_EOL;
                $sitemap .= '<g:description>' . htmlspecialchars($item->description) . '</g:description>' . PHP_EOL;
                $sitemap .= '<g:link>' . htmlspecialchars($link) . '</g:link>' . PHP_EOL;
                $sitemap .= '<g:image_link>' . htmlspecialchars($image_link) . '</g:image_link>' . PHP_EOL;
                $sitemap .= '<g:condition>New</g:condition>' . PHP_EOL;
                $sitemap .= '<g:availability>In stock</g:availability>' . PHP_EOL;
                $sitemap .= '<g:price>' . htmlspecialchars($finalAmount) . '</g:price>' . PHP_EOL;

                $sitemap .= '<g:product_type>' . 'Design Template' . '</g:product_type>' . PHP_EOL;
                $sitemap .= '<g:google_product_category>' . '3302' . '</g:google_product_category>' . PHP_EOL;
                $sitemap .= '<g:identifier_exists>' . 'false' . '</g:identifier_exists>' . PHP_EOL;
                $sitemap .= '<g:delivery_method>' . 'digital' . '</g:delivery_method>' . PHP_EOL;

                $sitemap .= '</item>' . PHP_EOL;
            }


        }

        $sitemap .= '
  </channel>';
        $sitemap .= '</rss>';

        return $sitemap;
    }

    function download(Request $request)
    {
        if ($request->isMethod('get')) {
            abort(403);
        } else {
            $isZip = 0;
            if ($request->hasHeader('path')) {
                $user_agent = $request->header('user-agent');
                $fromApp = $request->header('fromApp');
                $key = $request->header('key');
                $path = $request->header('path');
                $uid = $request->get('user_id');
            } else {
                $user_agent = $request->get('user-agent');
                $fromApp = $request->get('fromApp');
                $key = $request->get('key');
                $path = $request->get('path');
                $isZip = $request->has('isZip') ? $request->get('isZip') : 0;
                $uid = $request->get('user_id');
            }

            if ($key == null || $path == null || $user_agent == null || $fromApp == null) {
                return "1";
            }

            if (!$uid) {
                $uid = Carbon::now()->timestamp;
            }

            $user_agent_key = env('USER_AGENT', '1');
            $download_key = env('DOWNLOAD_KEY', '1');

            if ($download_key == $key && $user_agent_key == $user_agent && $fromApp == "1") {

                if ($isZip == 1) {
                    $zip = new \ZipArchive();
                    $filePath = '/uploadedFiles/d_zip_file/' . $uid . '-' . Carbon::now()->timestamp . '.zip';
                    $fileName = StorageUtils::get($filePath);
                    if ($zip->open($fileName, \ZipArchive::CREATE) == TRUE) {
                        try {
                            $array = json_decode($path);
                            foreach ($array as $key => $value) {
                                $relativeName = basename($value->file);
                                if (!StorageUtils::exists($value->file)) {
                                    $fontDatas = DB::table('fonts')->where('name', $this->getFileNameWithoutExtension(basename($value->file)))->first();
                                    if ($fontDatas) {
                                        $zip->addFile(StorageUtils::get($fontDatas->path), $value->folder . '/' . $relativeName);
                                    }
                                } else {
                                    $zip->addFile(StorageUtils::get($value->file), $value->folder . '/' . $relativeName);
                                }
                            }
                            $zip->close();
                        } catch (\Exception $e) {
                            return $value->file;

                        }
                    }

                    if (StorageUtils::exists($filePath)) {
                        return response()->download($fileName)->deleteFileAfterSend(true);
                    } else {
                        return "2";
                    }
                } else {
                    if (StorageUtils::exists($path)) {
                        return Storage::download($path);
                    } else {
                        return "3";
                    }
                }
            } else {
                return "4";
            }
        }
    }

    function downloadImages(Request $request)
    {
        if (Auth::check()) {
            $path = $request->get('path');
            $zip = new \ZipArchive();
            $filePath = '/uploadedFiles/d_zip_file/templates.zip';
            $fileName = StorageUtils::get($filePath);
            StorageUtils::delete($filePath);
            if ($zip->open($fileName, \ZipArchive::CREATE) == TRUE) {
                $array = json_decode($path);
                foreach ($array as $value) {
                    $relativeName = basename($value);
                    $zip->addFile(StorageUtils::get($value), $relativeName);
                }
                $zip->close();
            }

            if (StorageUtils::exists($filePath)) {
                return response()->json([
                    'success' => HelperController::$mediaUrl . $filePath
                ]);
            } else {
                return response()->json([
                    'error' => 'Error'
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Error'
            ]);
        }

    }

    public function vCatThumb($file, Request $request)
    {
        $path = "/uploadedFiles/vCatThumb/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function vThumb_file($file, Request $request)
    {
        $path = "/uploadedFiles/vThumb_file/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function getCaricatureCategoryFile($folder, $file, Request $request)
    {
        $path = "caricature/category/{$folder}/{$file}";
        return $this->getFile($path, $request,true);
    }

    public function serveCaricatureFile($folder, $type, $file, Request $request)
    {
        $path = "caricature/category/{$folder}/{$type}/{$file}";
        return $this->getFile($path, $request,true);
    }

    public function getAttireCategoryFile($folder, $file, Request $request)
    {
        $path = "caricature/attire/{$folder}/{$file}";
        return $this->getFile($path, $request,true);
    }

    public function serveAttireFile($folder, $type, $file, Request $request)
    {
        $path = "caricature/attire/{$folder}/{$type}/{$file}";
        return $this->getFile($path, $request,true);
    }

    public function v($file, Request $request)
    {
        $path = "/uploadedFiles/v/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function video_file($file, Request $request)
    {
        $path = "/uploadedFiles/video_file/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function vZip_file($file, Request $request)
    {
        $path = "/uploadedFiles/vZip_file/{$file}";
        if (StorageUtils::exists($path)) {
            return Storage::response($path);
        }
        abort(403);
    }

    public function d_zip_file($file, Request $request)
    {
        $path = "/uploadedFiles/d_zip_file/{$file}";
        if (StorageUtils::exists($path)) {
            return Storage::response($path);
        }
        abort(403);
    }

    public function font_file($file, Request $request)
    {
        $path = "/uploadedFiles/font_file/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function parse_file($file, Request $request)
    {
        $path = "/uploadedFiles/parse_file/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function sticker_file($file, Request $request)
    {
        $path = "/uploadedFiles/sticker_file/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function catThumb($file, Request $request)
    {
        $path = "/uploadedFiles/catThumb/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function thumb_file($file, Request $request)
    {
        $path = "/uploadedFiles/thumb_file/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function banner_file($file, Request $request)
    {
        $path = "/uploadedFiles/banner_file/{$file}";
        return $this->getFile($path, $request, true);
    }


    public function svg_thumb($file, Request $request)
    {
        $path = "/uploadedFiles/svg_thumb/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function svg_file($file, Request $request)
    {
        $path = "/uploadedFiles/svg_file/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function audio_thumb($file, Request $request)
    {
        $path = "/uploadedFiles/audio_thumb/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function audio_file($file, Request $request)
    {
        $path = "/uploadedFiles/audio_file/{$file}";
        return $this->getFile($path, $request, true);
    }


    public function cta_images($file, Request $request)
    {
        $path = "/uploadedFiles/cta_images/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function bg_file($file, Request $request)
    {
        $path = "/uploadedFiles/bg_file/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function font_thumb($file, Request $request)
    {
        $path = "/uploadedFiles/font_thumb/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function user_dp($file, Request $request)
    {
        $path = "/uploadedFiles/user_dp/{$file}";
        return $this->getFile($path, $request, true);
    }

    public function message_file($file, Request $request)
    {
        $path = "/uploadedFiles/message_file/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function contact_ss($file, Request $request)
    {
        $path = "/uploadedFiles/contact_ss/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function notifi_file($file, Request $request)
    {
        $path = "/uploadedFiles/notifi_file/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function customOrder($folder, $file, Request $request)
    {
        $path = "/uploadedFiles/customOrder/{$folder}/{$file}";
        return $this->getFile($path, $request, false);

    }

    public function brandKit($file, Request $request)
    {
        $path = "/uploadedFiles/brandKit/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function crafty_assets($file, Request $request)
    {
        $path = "/uploadedFiles/crafty_assets/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function designs($file, Request $request)
    {
        $path = "/uploadedFiles/fab_jsons/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function fab_designs($file, Request $request)
    {
        $path = "/uploadedFiles/fab_designs/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function draftTb($file, Request $request)
    {
        $path = "/uploadedFiles/draftTb/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function drafts($file, Request $request)
    {
        $path = "/uploadedFiles/drafts/{$file}";
        return $this->getFile($path, $request, false);
    }


    public function contentJson($folder, $file, Request $request)
    {
        $path = "/sp/${folder}/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function contentJson2($folder, $file, Request $request)
    {
        $path = "/sp/{$folder}/jn/{$file}/";
        return $this->getFile($path, $request, false);
    }

    public function contentJson3($folder, $file, Request $request)
    {
        $path = "/k/{$folder}/jn/{$file}/";
        return $this->getFile($path, $request, false);
    }

    public function contentJson4($folder, $file, Request $request)
    {
        $path = "/ct/{$folder}/jn/{$file}/";
        return $this->getFile($path, $request, false);
    }


    public function folderImage($folder, $file, Request $request)
    {
        $path = "/p/{$folder}/{$file}";
        return $this->getFile($path, $request, false);
    }


    public function faqsJson($file, Request $request)
    {
        $path = "/faqs/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function faqsJson2($folder, $file, Request $request)
    {
        $path = "/sp/{$folder}/fq/{$file}/";
        return $this->getFile($path, $request, false);
    }

    public function faqsJson3($folder, $file, Request $request)
    {
        $path = "/k/{$folder}/fq/{$file}/";
        return $this->getFile($path, $request, false);
    }

    public function faqsJson4($folder, $file, Request $request)
    {
        $path = "/ct/{$folder}/fq/{$file}/";
        return $this->getFile($path, $request, false);
    }


    public function frame_thumb($file, Request $request)
    {
        $path = "/uploadedFiles/frame_thumb/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function frame_file($file, Request $request)
    {
        $path = "/uploadedFiles/frame_file/{$file}";
        return $this->getFile($path, $request, false);
    }

    public function uploadedFiles()
    {
        abort(403);
    }

    // private function getFile($path, Request $request, $canResize) {
// if (Storage::exists($path)) {

    // $contents = Storage::get($path);

    // if ($canResize && Str::startsWith(Storage::mimeType($path), 'image/')) {
// $width = $request->input('width', null);
// $height = $request->input('height', null);
// $scaleVal = $request->input('resize', null);

    // if ($scaleVal) {
// $image = Image::make($contents);
// $originalWidth = $image->width();
// $originalHeight = $image->height();
// $newWidth = $originalWidth * $scaleVal;
// $newHeight = $originalHeight * $scaleVal;
// $image->resize($newWidth, $newHeight);
// $contents = $image->encode();
// } else {
// if ($width && $height) {
// $image = Image::make($contents);
// $image->resize($width, $height);
// $contents = $image->encode();
// }
// }
// }

    // $response = new Response($contents);
// $response->header('Access-Control-Allow-Origin', '*');
// $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
// $response->header('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
// $response->header('Content-Encoding', 'identity');
// $response->header('Content-Type', Storage::mimeType($path));
// return $response;
// }
// abort(403);
// }

    private function getFile($path, Request $request, $canResize)
    {
        if (StorageUtils::exists($path)) {

            $contents = Storage::get($path);
            $mimeType = Storage::mimeType($path);

            if ($canResize && Str::startsWith(Storage::mimeType($path), 'image/')) {
                $width = $request->input('width', null);
                $height = $request->input('height', null);
                $scaleVal = $request->input('resize', null);

                if ($scaleVal) {
                    $image = Image::make($contents);
                    $originalWidth = $image->width();
                    $originalHeight = $image->height();
                    $newWidth = $originalWidth * $scaleVal;
                    $newHeight = $originalHeight * $scaleVal;
                    $image->resize($newWidth, $newHeight);
                    $contents = $image->encode();
                } else {
                    if ($width && $height) {
                        $image = Image::make($contents);
                        $image->resize($width, $height);
                        $contents = $image->encode();
                    }
                }
                $response = new Response($contents);
                $response->header('Access-Control-Allow-Origin', '*');
                $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
                $response->header('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
                $response->header('Content-Encoding', 'identity');
                $response->header('Content-Type', $mimeType);
                return $response;
            }

            return response()->stream(function () use ($path) {
                $stream = Storage::readStream($path);
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Type' => Storage::mimeType($path),
                'Content-Length' => Storage::size($path),
                'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With',
                'Content-Encoding' => 'identity',

            ]);
        }
        abort(403);
    }

    private function getFileNameWithoutExtension($filename)
    {
        return substr($filename, 0, strrpos($filename, '.'));
    }
}