package client;

import flash.display.Bitmap;
import lib.encode.JPGEncoder;
import lib.upload.UploadPostHelper;
import flash.net.URLLoader;
import flash.net.URLLoaderDataFormat;
import flash.net.URLRequest;
import flash.net.URLRequestHeader;
import flash.net.URLRequestMethod;

class NoCameraBitmap extends Bitmap
     { public function new() { super(); } }

class Window {

	var webcam : Display;
	var noCamera:Bitmap;
	var takePhotoButton: flash.display.SimpleButton;
	var tbEnabled:Bool;
    var loader:URLLoader;
    
	public function new() {
		checkCamera();
	}

	private function checkCamera() {
		var cam = flash.media.Camera.getCamera();

		if(cam == null) {
		    if(noCamera == null) {
    	        noCamera = new NoCameraBitmap();
	            noCamera.width = flash.Lib.current.stage.stageWidth;
   		        noCamera.height = flash.Lib.current.stage.stageHeight;
   		        flash.Lib.current.addChild( noCamera );
   		    }
   		    haxe.Timer.delay(checkCamera,500);
   		    return;
		}
	
		connect();
	}
	
	private function connect() {
		var mc = flash.Lib.current;
		var st = mc.stage;

		webcam = new Webcam();
		webcam.width = st.stageWidth;
		webcam.height = st.stageHeight-30;
		mc.addChild(webcam);

		var t = new flash.text.TextField();
		t.text = "Take photo";
		t.width = st.stageWidth-1;
		t.height = 29;
		t.selectable = false;
		t.x = 2;
		var tf = new flash.text.TextFormat();
		tf.size = 20;
		tf.align = flash.text.TextFormatAlign.CENTER;
		t.setTextFormat(tf);

		var b = new flash.display.MovieClip();
		var colors = [0xF5F5F5, 0xC0C0C0];
        var alphas = [1, 1];
        var ratios = [0, 255];
        var matrix = new flash.geom.Matrix();
        matrix.createGradientBox(t.width-2, t.height-2, Math.PI/2, 0, 0);
        b.graphics.beginGradientFill(flash.display.GradientType.LINEAR, 
                                colors,
                                alphas,
                                ratios, 
                                matrix, 
                                flash.display.SpreadMethod.PAD, 
                                flash.display.InterpolationMethod.LINEAR_RGB, 
                                0);
//		b.graphics.beginFill(0xEEEEEE);
		b.graphics.lineStyle(1,0xA0A0A0);
		b.graphics.drawRect(0,0,t.width,29);
		b.graphics.endFill();
		b.addChild(t);

		var takePhotoButton = new flash.display.SimpleButton();
		takePhotoButton.upState = b;
		takePhotoButton.overState = b;
		takePhotoButton.downState = b;
		takePhotoButton.hitTestState = b;
		takePhotoButton.useHandCursor = true;
		takePhotoButton.addEventListener(flash.events.MouseEvent.CLICK,savePicture1);
		takePhotoButton.x = 0;
		takePhotoButton.y = st.stageHeight-30;
		mc.addChild(takePhotoButton);
	    tbEnabled = true;

	}
	
	private function savePicture1(e) {
	    if(!tbEnabled) return;
	    tbEnabled = false;
	    haxe.Timer.delay(savePicture2,1);
	}
	
	private function savePicture2() {
		var screen = webcam.grab();

        var jpgEncoder = new JPGEncoder(85);
        var jpgStream = jpgEncoder.encode(screen);

		if(flash.Lib.current.loaderInfo.parameters.cid==null) {
		    trace("No \"cid\" param passed");
		    return;
		}
		if(flash.Lib.current.loaderInfo.parameters.rid==null) {
		    trace("No \"rid\" param passed");
		    return;
		}
        var request:URLRequest = new URLRequest("modules/Premium/Warehouse/Items/Webcam/upload.php?cid="+flash.Lib.current.loaderInfo.parameters.cid+"&rid="+flash.Lib.current.loaderInfo.parameters.rid);
        request.data = UploadPostHelper.getPostData( 'image.jpg', jpgStream );
        request.method = URLRequestMethod.POST;
        request.requestHeaders.push( new URLRequestHeader( 'Cache-Control', 'no-cache' ) );
        request.requestHeaders.push(new URLRequestHeader('Content-Type', 'multipart/form-data; boundary=' + UploadPostHelper.getBoundary())); 

        loader = new URLLoader();
        loader.dataFormat = URLLoaderDataFormat.BINARY;
        loader.addEventListener(flash.events.Event.COMPLETE, onPictureUpload);
        loader.load(request);

        tbEnabled = true;
    }
    
    private function onPictureUpload(e) {
        var ret = loader.data.toString();
        if(ret=='') {
            flash.external.ExternalInterface.call('alert','Photo successfully uploaded ');
            flash.external.ExternalInterface.call('_chj','','','allow');
        } else
            flash.external.ExternalInterface.call('alert','Error: '+ret);
    }

}
