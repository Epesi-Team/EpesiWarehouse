package client;

import flash.display.BitmapData;

class Webcam extends Display {

	var cam : flash.media.Camera;

	public function new() {
		super();
		cam = flash.media.Camera.getCamera();
		if( cam == null ) {
		    trace("No camera found");
	    }
	    cam.setMode(1024,768,15);
		video.attachCamera(cam);
	}

    public function grab() {
		var screen = new BitmapData(Std.int(video.width),Std.int(video.height)); 
        screen.draw(video);
        return screen;
    }
}