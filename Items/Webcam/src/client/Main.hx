package client;

class Main {

	static var trace : flash.text.TextField;

	static function initTrace() {
		var mc = flash.Lib.current;
		trace = new flash.text.TextField();
		trace.y = 20;
		trace.thickness = 2;
		trace.width = mc.stage.stageWidth;
		trace.height = mc.stage.stageHeight - 20;
		trace.selectable = false;
		trace.textColor = 0xFFFFFF;
		trace.mouseEnabled = false;
		trace.filters = [new flash.filters.GlowFilter(0x7F7F7F,90,2,2,10)];
	}

	static function doTrace( v : Dynamic, ?pos : haxe.PosInfos ) {
		trace.text += pos.fileName+"("+pos.lineNumber+") : "+Std.string(v)+"\n";
//		haxe.Timer.delay(clearTrace,5000);
		flash.Lib.current.addChild(trace);
	}
	
	static function clearTrace() {
	    trace.text = "";
	}
	
	static function main() {
		initTrace();
		haxe.Log.trace = doTrace;

        var m = new Window();
	}

}
