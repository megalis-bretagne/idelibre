var MyBlobBuilder = function() {
  this.parts = [];
};

MyBlobBuilder.prototype.append = function(part) {
  this.parts.push(part);
  this.blob = undefined; // Invalidate the blob
};

MyBlobBuilder.prototype.getBlob = function() {
  if (!this.blob) {
    this.blob = new Blob(this.parts, { type: "text/plain" });
  }
  return this.blob;
};


/*
 * 
 * 
 * 
HOW TO USE

var myBlobBuilder = new MyBlobBuilder();

myBlobBuilder.append("Hello world, 2");

// Other stuff ... 

myBlobBuilder.append(",another data");
var bb = myBlobBuilder.getBlob();

*/