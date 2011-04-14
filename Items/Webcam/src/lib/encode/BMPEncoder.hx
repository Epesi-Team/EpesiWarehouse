/*
Copyright (c) 2007 Trevor McCauley - www.senocular.com

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
/*
	Ported to haXe by Baluta Cristian (http://ralcr.com/jpgencoder/)
*/
package lib.encode;

import flash.display.BitmapData;
import flash.geom.Matrix;
import flash.utils.ByteArray;
import flash.utils.Endian;

class BMPEncoder {
	
	/**
	 * Converts a BitmapData instance into a 32-bit
	 * BMP image.
	 * @param bitmapData A BitmapData instance of the image
	 * 		desired to have converted into a Bitmap (BMP).
	 * @return A ByteArray containing the binary Bitmap (BMP)
	 * 		representation of the BitmapData instance passed.
	 */
	public static function encode (bitmapData:BitmapData) :ByteArray {
		
		// image/file properties
		var bmpWidth :Int = bitmapData.width;
		var bmpHeight :Int = bitmapData.height;
		var imageBytes :ByteArray = bitmapData.getPixels (bitmapData.rect);
		var imageSize :Int = imageBytes.length;
		var imageDataOffset :Int = 0x36;
		var fileSize :Int = imageSize + imageDataOffset;
		
		// binary BMP data
		var bmpBytes = new ByteArray();
		bmpBytes.endian = Endian.LITTLE_ENDIAN; // byte order
		
		// header information
		bmpBytes.length = fileSize;
		bmpBytes.writeByte (0x42); // B
		bmpBytes.writeByte (0x4D); // M (BMP identifier)
		bmpBytes.writeInt (fileSize); // file size
		bmpBytes.position = 0x0A; // offset to image data
		bmpBytes.writeInt (imageDataOffset);
		bmpBytes.writeInt (0x28); // header size
		bmpBytes.position = 0x12; // width, height
		bmpBytes.writeInt (bmpWidth);
		bmpBytes.writeInt (bmpHeight);
		bmpBytes.writeShort (1); // planes (1)
		bmpBytes.writeShort (32); // color depth (32 bit)
		bmpBytes.writeInt (0); // compression type
		bmpBytes.writeInt (imageSize); // image data size
		//bmpBytes.position = imageDataOffset; // start of image data...
		//dpi 300*300
		bmpBytes.writeUnsignedInt (0x2e30); 
		bmpBytes.writeUnsignedInt (0x2e30);
		
		bmpBytes.position = imageDataOffset; // start of image data...
		
		// write pixel bytes in upside-down order
		// (as per BMP format)
		var col :Int = bmpWidth;
		var row :Int = bmpHeight;
		var rowLength :Int = col * 4; // 4 bytes per pixel (32 bit)
		try {
			// make sure we're starting at the
			// beginning of the image data
			imageBytes.position = 0;
			
			// bottom row up
			while (row >= 0) {
				// from end of file up to imageDataOffset
				bmpBytes.position = imageDataOffset + row*rowLength;
				
				// read through each column writing
				// those bits to the image in normal
				// left to rightorder
				col = bmpWidth;
				while (col >= 0) {
					bmpBytes.writeInt (imageBytes.readInt());
					col--;
				}
				row--;
			}
		} catch (error:Dynamic){
			// end of file
		}
		
		// return BMP file
		return bmpBytes;
	}
}