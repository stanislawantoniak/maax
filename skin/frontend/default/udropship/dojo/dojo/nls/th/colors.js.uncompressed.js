define(
"dojo/nls/th/colors", ({
// local representation of all CSS3 named colors, companion to dojo.colors.  To be used where descriptive information
// is required for each color, such as a palette widget, and not for specifying color programatically.

//Note: due to the SVG 1.0 spec additions, some of these are alternate spellings for the same color (e.g. gray / grey).
//TODO: should we be using unique rgb values as keys instead and avoid these duplicates, or rely on the caller to do the reverse mapping?
aliceblue: "ฟ้าจาง",
antiquewhite: "สีเนื้อ",
aqua: "ฟ้าน้ำทะเล",
aquamarine: "อะความารีน",
azure: "น้ำเงินฟ้า",
beige: "น้ำตาลเบจ",
bisque: "ขาวข้าวสาร",
black: "ดำ",
blanchedalmond: "เนื้ออ่อน",
blue: "น้ำเงิน",
blueviolet: "น้ำเงินม่วง",
brown: "น้ำตาล",
burlywood: "น้ำตาลอ่อน",
cadetblue: "เขียวน้ำเงินหม่น",
chartreuse: "เขียวสะท้อนแสง",
chocolate: "ช็อกโกแลต",
coral: "แสดเข้มนวล",
cornflowerblue: "สีคอร์นฟลาวเวอร์บลู",
cornsilk: "cornsilk",
crimson: "แดงเลือดหมู",
cyan: "เขียวแกมน้ำเงิน",
darkblue: "น้ำเงินเข้ม",
darkcyan: "เขียวแกมน้ำเงินเข้ม",
darkgoldenrod: "ทองเหลืองเข้ม",
darkgray: "เทาเข้ม",
darkgreen: "เขียวเข้ม",
darkgrey: "เทาเข้ม", // same as darkgray
darkkhaki: "กากีเข้ม",
darkmagenta: "แดงแกมม่วงเข้ม",
darkolivegreen: "เขียวโอลีฟเข้ม",
darkorange: "ส้มเข้ม",
darkorchid: "สีม่วงกล้วยไม้เข้ม",
darkred: "แดงเข้ม",
darksalmon: "ส้มเข้ม",
darkseagreen: "เขียวทะเลเข้ม",
darkslateblue: "น้ำเงินนวลเข้ม",
darkslategray: "เทานวลเข้ม",
darkslategrey: "เทานวลเข้ม", // same as darkslategray
darkturquoise: "ฟ้าขี้นกการเวกเข้ม",
darkviolet: "ม่วงเข้ม",
deeppink: "ชมพูเข้ม",
deepskyblue: "ฟ้าสด",
dimgray: "เทาทึม",
dimgrey: "เทาทึม", // same as dimgray
dodgerblue: "ฟ้าสะท้อนแสง",
firebrick: "สีอิฐ",
floralwhite: "ขาวแกมชมพู",
forestgreen: "หยก",
fuchsia: "บานเย็น",
gainsboro: "เทานวล",
ghostwhite: "น้ำข้าว",
gold: "ทอง",
goldenrod: "ทองเหลือง",
gray: "เทา",
green: "เขียว",
greenyellow: "เขียวแกมเหลือง",
grey: "เทา", // same as gray
honeydew: "ขาวแกมเขียว",
hotpink: "ชมพูจัด",
indianred: "แดงอมเหลือง",
indigo: "คราม",
ivory: "งาช้าง",
khaki: "กากี",
lavender: "ม่วงลาเวนเดอร์",
lavenderblush: "นมเย็น",
lawngreen: "เขียวหญ้าอ่อน",
lemonchiffon: "lemon chiffon",
lightblue: "น้ำเงินอ่อน",
lightcoral: "ชมพูอมแดง",
lightcyan: "เขียวแกมน้ำเงินอ่อน",
lightgoldenrodyellow: "ทองเหลืองอ่อน",
lightgray: "เทาอ่อน",
lightgreen: "เขียวอ่อน",
lightgrey: "เทาอ่อน", // same as lightgray
lightpink: "ชมพูอ่อน",
lightsalmon: "ส้มจาง",
lightseagreen: "เขียวทะเลอ่อน",
lightskyblue: "ฟ้าอ่อน",
lightslategray: "เทานวลอ่อน",
lightslategrey: "เทานวลอ่อน", // same as lightslategray
lightsteelblue: "น้ำเงินนวลอ่อน",
lightyellow: "เหลืองอ่อน",
lime: "เหลืองมะนาว",
limegreen: "เขียวมะนาว",
linen: "ลินนิน",
magenta: "แดงแกมม่วง",
maroon: "น้ำตาลแดง",
mediumaquamarine: "อะความารีนกลางๆ",
mediumblue: "น้ำเงินกลางๆ",
mediumorchid: "ม่วงกล้วยไม้กลางๆ",
mediumpurple: "ม่วงอัญชัญ",
mediumseagreen: " เขียวทะเลกลางๆ",
mediumslateblue: "น้ำเงินนวลกลางๆ",
mediumspringgreen: "สีเขียวนวลกลางๆ",
mediumturquoise: "ฟ้าขี้นกการเวกกลางๆ",
mediumvioletred: "แดงอมม่วงกลางๆ",
midnightblue: "น้ำเงินทึบ",
mintcream: "ขาวกะทิ",
mistyrose: "ชมพูหม่น",
moccasin: "ม็อคค่า",
navajowhite: "ส้มหนังกลับ",
navy: "น้ำเงินเข้ม",
oldlace: "ขาวนวล",
olive: "โอลีฟ",
olivedrab: "เขียวมะกอกแก่",
orange: "ส้ม",
orangered: "ส้มแกมแดง",
orchid: "สีกล้วยไม้",
palegoldenrod: "ทองเหลืองจาง",
palegreen: "เขียวจาง",
paleturquoise: "ฟ้าขี้นกการเวกจาง",
palevioletred: "แดงอมม่วงจาง",
papayawhip: "ชมพูจาง",
peachpuff: " สีพีช",
peru: "ส้มดินเผา",
pink: "ชมพู",
plum: "ม่วงอ่อน",
powderblue: "ฟ้าหม่น",
purple: "ม่วง",
red: "แดง",
rosybrown: "กะปิ",
royalblue: "น้ำเงินเข้ม",
saddlebrown: "น้ำตาล",
salmon: "ส้มอ่อน",
sandybrown: "น้ำตาลลูกรัง",
seagreen: "เขียวทะเล",
seashell: "สีขาวหอยทะเล",
sienna: "น้ำตาลอมแดง",
silver: "เงิน",
skyblue: "ฟ้า",
slateblue: "น้ำเงินนวล",
slategray: "เทาอมน้ำเงินนวล",
slategrey: "เทาอมน้ำเงินนวล", // same as slategray
snow: "ขาวหิมะ",
springgreen: "เขียว",
steelblue: "น้ำเงินด้าน",
tan: "แทน",
teal: "เขียวหัวเป็ด",
thistle: "ม่วงจาง",
tomato: "แสด",
transparent: "สีใส",
turquoise: "ฟ้าขี้นกการเวก",
violet: "ม่วง",
wheat: "เหลืองรำข้าว",
white: "ขาว",
whitesmoke: "ขาวควัน",
yellow: "เหลือง",
yellowgreen: "เหลืองแกมเขียว"
})
);
