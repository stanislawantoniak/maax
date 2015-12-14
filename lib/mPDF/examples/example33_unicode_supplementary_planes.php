<?php
ini_set("memory_limit","128M");


$html = '
<style>
body {
	font-family: sans-serif;
	font-size: 10pt;
}
h4 {
	font-variant: small-caps; 
}
h5 {
	margin-bottom: 0;
	color: #110044;
}
dl {
	margin: 0;
}
</style>
<h1></a>mPDF</h1>
<h2>Unicode Supplementary Planes</h2>
<p>The original Unicode allocated characters between x0000 and xFFFF (65,536 characters). This \'Basic Multilingual Plane\' supported most characters in common use, including a large number of Unified Chinese-Japanese-Korean characters (CJK). Later the Unicode standard was extended to 16 Planes. </p>

<p>The first plane (plane 0), the Basic Multilingual Plane (BMP), is where most characters have been assigned so far.</p>
<p>Plane 1, the Supplementary Multilingual Plane (SMP), is mostly used for historic scripts such as Linear B, but is also used for musical and mathematical symbols.</p>
<p>Plane 2, the Supplementary Ideographic Plane (SIP), is used for about 40,000 Unified Han (CJK) Ideographs.</p>

<p>mPDF supports fonts containing characters from all Unicode Planes. By choosing the correct font, almost every single character from Unicode 5 can be displayed in a PDF file.</p>

<h4>Unicode Supplementary Multilingual Plane (SMP or Plane 1) U+10000 - U+1FFFF</h4>
<h5>Gothic text</h5>
<div>
This paragraph shows Gothic text. These characters lie in the Unicode Supplementary Multilingual Plane U+10330 - U+1034F.
</div>
<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE;">
<div style="font-family:damase">
&#66352; &#66353; &#66354; &#66355; &#66356; &#66357; &#66358; &#66359; &#66360; &#66361; &#66362; &#66363; &#66364; &#66365; &#66366; &#66367; &#66368; &#66369; &#66370; &#66371; &#66372; &#66373; &#66374; &#66375; &#66376; &#66377; &#66378;
</div>
</div>
<div>
Font: MPH2BDamase (damase_v.2.ttf) available from: http://www.wazu.jp/gallery/views/View_MPH2BDamase.html 
</div>

<h5>Egyptian Hieroglyphics</h5>
<div>
This paragraph shows Egyptian Hieroglyphics. These characters lie in the Unicode Supplementary Multilingual Plane U+13000 - U+1342F.
</div>
<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE;">
<div style="font-family:aegyptus; font-size: 24pt">
&#77824; &#77825; &#77826; &#77827; &#77828; &#77829; &#77830; &#77831; &#77832; &#77833; &#77834; &#77835; &#77836; &#77837; &#77838; &#77839; &#77840; &#77841;
</div>
</div>
<div>
Font: Aegyptus.otf available from: http://users.teilar.gr/~g1951d/
</div>

<p>SMP contains mainly ancient scripts - see <a href="http://mpdf1.com/manual/index.php?tid=451">http://mpdf1.com/manual/index.php?tid=451</a> for full list.</p>

<p>mPDF uses a different method to embed fonts in the PDF file if they include characters from SMP or SIP, because the characters cannot be represented by a 4 character hex code 0000-FFFF. This method is less eficient than the default method, and it can be suppressed by adding the font name to the array \'BMPonly\' in the config_fonts.php configuration file. </p>

<p>Note that the DejaVu fonts distributed with mPDF and (GNU)FreeSans and FreeSerif fonts do contain a few characters in the SMP plane, but most users will not require them and by default they have been added to the array \'BMPonly\'.</p>

<pagebreak />

<h4>CJK characters</h4>
<p>Below are examples of all the CJK Unicode blocks contained in the Basic Multilingual Plane and Supplemental Ideographic Plane</p>

<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE;">
<h5>Plane 0 (BMP)</h5>
<dl style="font-family:sun-exta;">
<dt>CJK Radicals Supplement</dt>
<dd>&#11918; &#11919; &#11920; &#11921; &#11922; &#11923; &#11924; &#11925; &#11926; &#11927; &#11928; &#11929; &#11931; &#11932; &#11933; &#11934; &#11935; &#11936; &#11937; &#11938; &#11939; &#11940; &#11941; &#11942; &#11943; &#11944; &#11945; &#11946;  </dd>
<dt>Kangxi Radicals</dt>
<dd>&#12083; &#12084; &#12085; &#12086; &#12087; &#12088; &#12089; &#12090; &#12091; &#12092; &#12093; &#12094; &#12095; &#12096; &#12097; &#12098; &#12099; &#12100; &#12101; &#12102; &#12103; &#12104; &#12105; &#12106; &#12107; &#12108; &#12109;  </dd>
<dt>Ideographic Description Characters</dt>
<dd>&#12272; &#12273; &#12274; &#12275; &#12276; &#12277; &#12278; &#12279; &#12280; &#12281; &#12282; &#12283; </dd>
<dt>CJK Symbols and Punctuation</dt>
<dd>&#12288;&#12296; &#12297; &#12298; &#12299; &#12300; &#12301; &#12302; &#12303; &#12304; &#12305; &#12306; &#12307; &#12308; &#12309; &#12310; &#12311; &#12312; &#12313; &#12314; &#12315; &#12316; &#12317; &#12318; &#12326; &#12327; &#12328; &#12329; &#12330; </dd>
<dt>Hiragana</dt>
<dd>&#12353; &#12354; &#12355; &#12356; &#12357; &#12358; &#12359; &#12360; &#12361; &#12362; &#12363; &#12364; &#12365; &#12366; &#12367; &#12368; &#12369; &#12370; &#12371; &#12372; &#12373; &#12374; </dd>
<dt>Katakana</dt>
<dd>&#12448; &#12449; &#12450; &#12451; &#12452; &#12453; &#12454; &#12455; &#12456; &#12457; &#12458; &#12459; &#12460; &#12461; &#12462; &#12463; &#12464; &#12465; &#12466; &#12467; &#12468; </dd>
<dt>Bopomofo</dt>
<dd>&#12549; &#12550; &#12551; &#12552; &#12553; &#12554; &#12555; &#12556; &#12557; &#12558; &#12559; &#12560; &#12561; &#12562; &#12563; &#12564; &#12565; &#12566; </dd>
<dt>Hangul Compatibility Jamo</dt>
<dd>&#12593; &#12594; &#12595; &#12596; &#12597; &#12598; &#12599; &#12600; &#12601; &#12602; &#12603; &#12604; &#12605; &#12606; &#12607; &#12608; &#12609; &#12610; </dd>
<dt>Kanbun</dt>
<dd>&#12688; &#12689; &#12690; &#12691; &#12692; &#12693; &#12694; &#12695; &#12696; &#12697; &#12698; &#12699; &#12700; &#12701; &#12702; &#12703; </dd>
<dt>Bopomofo Extended</dt>
<dd>&#12704; &#12705; &#12706; &#12707; &#12708; &#12709; &#12710; &#12711; &#12712; &#12713; &#12714; &#12715; &#12716; &#12717; &#12718; &#12719; &#12720; &#12721; &#12722; &#12723; &#12724; &#12725; &#12726; &#12727; </dd>
<dt>Katakana Phonetic Extensions</dt>
<dd>&#12784; &#12785; &#12786; &#12787; &#12788; &#12789; &#12790; &#12791; &#12792; &#12793; &#12794; &#12795; &#12796; &#12797; &#12798; &#12799; </dd>
<dt>Enclosed CJK Letters and Months</dt>
<dd>&#12804; &#12805; &#12806; &#12807; &#12808; &#12809; &#12810; &#12811; &#12812; &#12813; &#12814; &#12815; &#12816; &#12817; &#12818; &#12819; &#12820; &#12821; &#12822; &#12823; &#12824; &#12825; &#12826; &#12827; &#12828; </dd>
<dt>CJK Compatibility</dt>
<dd>&#13056; &#13057; &#13058; &#13059; &#13060; &#13061; &#13062; &#13063; &#13064; &#13065; &#13066; &#13067; &#13068; &#13069; &#13070; &#13071; &#13072; &#13073; &#13074; &#13075; &#13076; &#13077; &#13078; &#13079; &#13080; </dd>
<dt>CJK Unified Ideographs Extension A</dt>
<dd>&#13312; &#13313; &#13314; &#13315; &#13316; &#13317; &#13318; &#13319; &#13320; &#13321; &#13322; &#13323; &#13324; &#13325; &#13326; &#13327; &#13328; &#13329; &#13330; &#13331; &#13332; &#13333; &#13334; &#13335; &#13336; </dd>
<dt>CJK Unified Ideographs</dt>
<dd>&#19968; &#19969; &#19970; &#19971; &#19972; &#19973; &#19974; &#19975; &#19976; &#19977; &#19978; &#19979; &#19980; &#19981; &#19982; &#19983; &#19984; &#19985; &#19986; &#19987; &#19988; &#19989; &#19990; &#19991; &#19992; </dd>
<dt>Yi Syllables</dt>
<dd>&#40960; &#40961; &#40962; &#40963; &#40964; &#40965; &#40966; &#40967; &#40968; &#40969; &#40970; &#40971; &#40972; &#40973; &#40974; &#40975; &#40976; &#40977; &#40978; &#40979; &#40980; &#40981; &#40982; &#40983; &#40984; &#40985; &#40986;  </dd>
<dt>Yi Radicals</dt>
<dd>&#42128; &#42129; &#42130; &#42131; &#42132; &#42133; &#42134; &#42135; &#42136; &#42137; &#42138; &#42139; &#42140; &#42141; &#42142; &#42143; &#42144; &#42145; &#42146; &#42147; &#42148; &#42149; &#42150; &#42151; &#42152; &#42153;  </dd>
<dt>Hangul Syllables</dt>
<dd>&#44032; &#44033; &#44034; &#44035; &#44036; &#44037; &#44038; &#44039; &#44040; &#44041; &#44042; &#44043; &#44044; &#44045; &#44046; &#44047; &#44048; &#44049; &#44050; &#44051; &#44052; &#44053; &#44054; &#44055; &#44056; </dd>
<dt>CJK Compatibility Ideographs</dt>
<dd>&#63744; &#63745; &#63746; &#63747; &#63748; &#63749; &#63750; &#63752; &#63753; &#63754; &#63755; &#63756; &#63757; &#63758; &#63759; &#63760; &#63761; &#63762; &#63763; &#63764; &#63765; &#63766; &#63767; &#63768; &#63769; </dd>
</dl>

<h5>Plane 2 (SIP)</h5>
<dl style="font-family:sun-exta;">
<dt>CJK Unified Ideographs Extension B</dt>
<dd>&#x20000; &#x20001; &#x20002; &#x20003; &#x20004; &#x20005; &#x20006; &#x20007; &#x20008; &#x20009; &#x2000A; &#x2000B; &#x2000C; &#x2000D; &#x2000E; &#x2000F; &#x20010; &#x20011; &#x20012; &#x20013; </dd>

<dt>CJK Unified Ideographs Extension C</dt>
<dd>&#x2A700; &#x2A701; &#x2A702; &#x2A703; &#x2A704; &#x2A705; &#x2A706; &#x2A707; &#x2A708; &#x2A709; &#x2A70A; &#x2A70B; &#x2A70C; &#x2A70D; &#x2A70E; &#x2A70F; &#x2A710; &#x2A711; &#x2A712; &#x2A713; </dd>

<dt>CJK Compatibility Ideographs Supplement</dt>
<dd>&#x2F800; &#x2F801; &#x2F802; &#x2F803; &#x2F804; &#x2F805; &#x2F806; &#x2F807; &#x2F808; &#x2F809; &#x2F80A; &#x2F80B; &#x2F80C; &#x2F80D; &#x2F80E; &#x2F80F; &#x2F810; &#x2F811; &#x2F812; &#x2F813; </dd>
</dl>

</div>

<pagebreak />

<h4>Using CJK fonts in mPDF</h4>

<p>Fonts containing CJK characters are large files, typically 10-30MB. Adobe provides a free download of an \'Asian font pack\' allowing you to create PDF files without including (embedding) the font information in the file. This keeps the file size to a minimum and minimises resource usage on your website generating the PDF file. However, users will have to download the Adobe font packs to read the file, and other PDF software will not display the text correctly.</p>

<p>mPDF allows you to embed subsets of CJK fonts keeping file size down, although there is increased memory usage to generate these files.</p>

<p>Some CJK fonts are broken up into 2 files because of the size of the files. One freely available font with almost complete coverage of all CJK characters (in both BMP and SIP) is \'Sun\' available from Alan Wood\'s excellent website: <a href="http://www.alanwood.net/unicode/fonts-east-asian.html">http://www.alanwood.net/unicode/fonts-east-asian.html</a>. This comes as 2 files, Sun-ExtA and Sun-ExtB (both about 20MB in size) containing the characters from BMP and SIP respectively.
</p>

<p>mPDF allows you to treat these as one font by defining the second file as an SIP-extension of the first in the config_fonts.php configuration file. The following text includes random characters from the BMP and SIP mixed together:</p>

<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE;">
<div style="font-family:sun-extA;">
&#40706; &#40712; &#40727; &#x2320f; &#x23225; &#40742; &#40743; &#x2322f; &#x23231; &#40761; &#40772; &#x23232; &#x23233; &#40773; &#40784; &#x23234; &#x23256; &#40787; &#40794; &#x23262; &#x23281; &#40802; &#40809; &#x23289; &#x2328a; 
</div>
</div>

<p>This is the entry in the config_fonts.php configuration file:</p>
<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE; font-family: \'Courier new\'; font-size: 8.5pt;">
$this->fontdata = array(<br />
...<br />
 &nbsp; &nbsp; &nbsp; "sun-exta" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "Sun-ExtA.ttf",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'sip-ext\' => \'sun-extb\',<br />
 &nbsp; &nbsp; &nbsp;  ),<br />
 &nbsp; &nbsp; &nbsp; "sun-extb" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "Sun-ExtB.ttf",<br />
 &nbsp; &nbsp; &nbsp;  ),<br />
<br />
...<br />
);
</div>


<p>This is the HTML code - note only the sun-exta font-family needs to be referenced:</p>
<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE; font-family: \'Courier new\'; font-size: 8.5pt;">
&lt;div style="font-family:sun-extA;"&gt;
&amp;#40706; &amp;#40712; &amp;#40727; &amp;#x2320f; &amp;#x23225; &amp;#40742; &amp;#40743; &amp;#x2322f; &amp;#x23231; &amp;#40761; &amp;#40772; &amp;#x23232; &amp;#x23233; &amp;#40773; &amp;#40784; &amp;#x23234; &amp;#x23256; &amp;#40787; &amp;#40794; &amp;#x23262; &amp;#x23281; &amp;#40802; &amp;#40809; &amp;#x23289; &amp;#x2328a; 
&lt;/div&gt;
</div>

<p>NB You may also need to edit the value $this->useAdobeCJK=false in config.php or use new mPDF(\'-aCJK\'), and edit the config_cp.php configuration file.</p>


<pagebreak />

<h4>TrueType Collections</h4>

<p>TrueType Collections (.ttc files) contain more than one font. mPDF treats each font separately by defining the TTCfontID array in the config_fonts.php configuration file. </p>

<p>This example uses the Windows MingLiU fonts, which consist of 2 files containing 6 fonts (note that mingliub is not a Bold variant): </p>

<p>Font collection file (mingliu.ttc) contains the following fonts:<br />
[1] MingLiU (mingliu) Regular<br />
[2] PMingLiU (pmingliu) Regular (Proportional)<br />
[3] MingLiU_HKSCS (mingliu_hkscs) Regular<br />
</p>

<p>Font collection file (mingliub.ttc) contains the following fonts:<br />
[1] MingLiU-ExtB (mingliu-extb) Regular<br />
[2] PMingLiU-ExtB (pmingliu-extb) Regular (Proportional)<br />
[3] MingLiU_HKSCS-ExtB (mingliu_hkscs-extb) Regular<br />
</p>

<p>This is a sample entry in the config_fonts.php configuration file:</p>
<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE; font-family: \'Courier new\'; font-size: 8.5pt;">
$this->fontdata = array(<br />
 &nbsp; &nbsp; &nbsp; "mingliu" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "mingliu.ttc",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'TTCfontID\' => array (<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \'R\' => 1,<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; ),<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'sip-ext\' => \'mingliu-extb\',<br />
 &nbsp; &nbsp; &nbsp;  ),<br />

 &nbsp; &nbsp; &nbsp; "pmingliu" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "mingliu.ttc",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'TTCfontID\' => array (<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \'R\' => 2,<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; ),<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'sip-ext\' => \'pmingliu-extb\',<br />
 &nbsp; &nbsp; &nbsp;  ),<br />

 &nbsp; &nbsp; &nbsp; "mingliu_hkscs" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "mingliu.ttc",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'TTCfontID\' => array (<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \'R\' => 3,<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; ),<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'sip-ext\' => \'mingliu_hkscs-extb\',<br />
 &nbsp; &nbsp; &nbsp;  ),<br />

 &nbsp; &nbsp; &nbsp; "mingliu-extb" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "mingliub.ttc",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'TTCfontID\' => array (<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \'R\' => 1,<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; ),<br />
 &nbsp; &nbsp; &nbsp;  ),<br />

 &nbsp; &nbsp; &nbsp; "pmingliu-extb" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "mingliub.ttc",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'TTCfontID\' => array (<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \'R\' => 2,<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; ),<br />
 &nbsp; &nbsp; &nbsp;  ),<br />

 &nbsp; &nbsp; &nbsp; "mingliu_hkscs-extb" => array(<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'R\' => "mingliub.ttc",<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; \'TTCfontID\' => array (<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \'R\' => 3,<br />
 &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp; ),<br />
 &nbsp; &nbsp; &nbsp;  ),<br />
);
</div>


<p>This is sample HTML code:</p>
<div style="border:0.2mm solid #000088; padding: 1em; background-color: #EEEEEE; font-family: \'Courier new\'; font-size: 8.5pt;">
&lt;div style="font-family:mingliu;"&gt;
&amp;#40706; &amp;#40742; &amp;#40772; &amp;#40784; &amp;#40802; &amp;#40809; &amp;#x23289; &amp;#x2328a; 
&lt;/div&gt;
<br />
&lt;div style="font-family:mingliu_hkscs;"&gt;
&amp;#40706; &amp;#40742; &amp;#40772; &amp;#40784; &amp;#40802; &amp;#40809; &amp;#x23289; &amp;#x2328a; 
&lt;/div&gt;
<br />
&lt;div style="font-family:pmingliu;"&gt;
&amp;#40706; &amp;#40742; &amp;#40772; &amp;#40784; &amp;#40802; &amp;#40809; &amp;#x23289; &amp;#x2328a; 
&lt;/div&gt;
</div>


';

//==============================================================
//==============================================================
//==============================================================
//==============================================================
//==============================================================

include("../mpdf.php");

$mpdf=new mPDF(); 

$mpdf->WriteHTML($html);

$mpdf->Output();
exit;

//==============================================================
//==============================================================
//==============================================================


?>