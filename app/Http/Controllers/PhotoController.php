<?php

namespace App\Http\Controllers;

use File;
use Auth;
use Response;
use Log;
use App\Models\Image;
use Intervention\Image\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;

class PhotoController extends Controller
{

    protected $upload_path;
    protected $avatar_path;

    public function __construct()
    {
        $this->upload_path = base_path().'/'.env('UPLOAD_PATH');
        $this->avatar_path = base_path().'/'.env('AVATAR_PATH');
    }

    public function upload()
    {
        $data = Input::all();
        $validator = Validator::make($data, Image::$rules, Image::$messages);

        if ($validator->fails()) {

            return Response::json([
                'status' => 'error',
                'message' => $validator->messages()->first(),
            ], 200);
        }

        $photo = $data['img'];

        $original_name = $photo->getClientOriginalName();
        $original_name_without_ext = substr($original_name, 0, strlen($original_name) - 4);

        $filename = $this->sanitize($original_name_without_ext);
        $allowed_filename = $this->createUniqueFilename( $filename );

        $filename_ext = $allowed_filename .'.jpg';

        $manager = new ImageManager();
        $image = $manager->make( $photo )
                ->resize(300, null, function( $constraint ) {
                    $constraint->aspectRatio();
                })
                ->encode('jpg')
                ->save($this->upload_path.'/'. $filename_ext);

        if( !$image) {
            return Response::json([
                'status' => 'error',
                'message' => 'Server error while uploading',
            ], 200);

        }

        return Response::json([
            'status'    => 'success',
            'url'       => env('UPLOAD_URL') . '/' . $filename_ext,
            'width'     => $image->width(),
            'height'    => $image->height()
        ], 200);
    }

    public function crop()
    {
        $data = Input::all();
        $image_url = $data['imgUrl'];

        // resized sizes
        $imgW = $data['imgW'];
        $imgH = $data['imgH'];
        // offsets
        $imgY1 = $data['imgY1'];
        $imgX1 = $data['imgX1'];
        // crop box
        $cropW = $data['width'];
        $cropH = $data['height'];
        // rotation angle
        $angle = $data['rotation'];

        $filename_array = explode('/', $image_url);
        $filename = $filename_array[sizeof($filename_array)-1];
        $filepath = $this->upload_path.'/'.$filename;

        Log::info('Try to manipulate photo : '.$filepath.' for user : '.Auth::user()->name);

        $manager = new ImageManager();
        $image = $manager->make( $filepath );

        Log::info('Fiinsh manager->make(..) for : '.$filepath);

        $image->resize($imgW, $imgH)
            ->rotate(-$angle)
            ->crop($cropW, $cropH, $imgX1, $imgY1)
            ->save($this->avatar_path  . '/' . $filename);

        if( !$image) {

            return Response::json([
                'status' => 'error',
                'message' => 'Server error while cropping',
            ], 200);

        }

        $user = Auth::user();
        $user->avatar = $filename;
        $user->save();

        return Response::json([
            'status' => 'success',
            'url' => env('AVATAR_URL') . '/'.$filename
        ], 200);
    }


    private function sanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;

        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }


    private function createUniqueFilename( $filename )
    {
        $upload_path = env('UPLOAD_PATH');
        $full_image_path = $upload_path .'/'. $filename . '.jpg';

        if ( File::exists( $full_image_path ) )
        {
            // Generate token for image
            $image_token = substr(sha1(mt_rand()), 0, 5);
            return $filename . '-' . $image_token;
        }

        return $filename;
    }
}
