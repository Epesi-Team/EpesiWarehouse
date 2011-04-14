package client;

class Display extends flash.display.MovieClip {

	var video : flash.media.Video;

	public function new() {
		super();
		video = new flash.media.Video(Std.int(width),Std.int(height));
		addChild(video);
		this.addEventListener(flash.events.Event.RESIZE,onResize);
		onResize(null);
	}

	function onResize(e) {
		video.width = width;
		video.height = height;
	}

	public function doStop() {
	}

}