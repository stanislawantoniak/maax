<!-- START SIDEBAR -->

<div class="current-filter visible-xs">
      <dl class="clearfix">
           <dt>Aktualnie ogladasz:</dt>
           <dd>
           <span class="label">Rozmiar: <span class="value">M</span><i class="fa fa-times"></i></span>
           <span class="label">Rozmiar: <span class="value">M</span><i class="fa fa-times"></i></span> 
           <span class="label">Marka: <span class="value">Adidas</span><i class="fa fa-times"></i></span>
           <span class="label">Marka: <span class="value">Adidas</span><i class="fa fa-times"></i></span>
           </dd>
           <dd class="action"><a href="#" class="button button-third">wyczyść wszystko</a></dd>
         </dl>
    </div>
<div class="sidebar clearfix">
  
  <div class="block-filter test">
    <!-- start section FILTER CATEGORY-->
    <div class="section clearfix">
      <h3 class="open">Kategorie <i class="fa fa-chevron-up "></i></h3>
      <div class="content">
        <dl>
          <dt>Sukienki</dt>
          <dd><a href="#" class="simpleChild"><span >Lorem Ipsum is simply dummy printing</span>&nbsp;<span class="suffix">(200)</span></a></dd>
          <dd><a href="#" class="simpleChild"><span >Torebki</span> <span class="suffix">(200)</span></a></dd>
          <dd><a href="#" class="simpleChild"><span >Torebki</span> <span class="suffix">(200)</span></a></dd>
          <dd><a href="#" class="simpleChild"><span >Torebki</span> <span class="suffix">(200)</span></a></dd>
        </dl>
         <div class="action clearfix">
           <a href="#" class="go_back underline external">wróć do kategorii Ona</a>
         </div>
      </div>
     
    </div>
    <!-- start section FILTER CATEGORY-->
    <?php include '_include/sidebar-menu-category.inc'; ?>
    <form action="#" id="form-filter">
     <!-- start section FILTER SIZE-->
     <?php //require '_include/filter/size.inc'; ?>
    <div class="section clearfix">
    
      <h3 class="open">Rozmiar <i class="fa fa-chevron-up"></i></h3>
      <div class="content"  id="filter_size">
        <label for="size_xs">
          <input type="checkbox" id="size_xs" name="size[xs]"/><span>XS</span>
        </label>
        <label for="size_s">
          <input type="checkbox" id="size_s" name="size[s]"/><span>S</span>
        </label>
         <label for="size_m">
          <input type="checkbox" id="size_m" name="size[m]"/><span>M</span>
        </label>
         <label for="size_l">
          <input type="checkbox" id="size_l" name="size[l]"/><span>L</span>
        </label>
         <label for="size_xl">
          <input type="checkbox" id="size_xl" name="size[xl]"/><span>XL</span>
        </label>
         <label for="size_xxl">
          <input type="checkbox" id="size_xxl" name="size[xxl]"/><span>XXL</span>
        </label>

         <label for="size_36">
          <input type="checkbox" id="size_36" name="size[36]"/><span>36</span>
        </label>
        <label for="size_38">
          <input type="checkbox" id="size_38" name="size[38]"/><span>38</span>
        </label>
        <label for="size_40">
          <input type="checkbox" id="size_40" name="size[40]"/><span>40</span>
        </label>
        <label for="size_42">
          <input type="checkbox" id="size_42" name="size[42]"/><span>42</span>
        </label>
        <label for="size_44">
          <input type="checkbox" id="size_44" name="size[44]"/><span>44</span>
        </label>
        <label for="size_46">
          <input type="checkbox" id="size_46" name="size[46]"/><span>46</span>
        </label>
        <label for="size_48">
          <input type="checkbox" id="size_48" name="size[48]"/><span>48</span>
        </label>
        <label for="size_50">
          <input type="checkbox" id="size_50" name="size[50]"/><span>50</span>
        </label>
        <label for="size_52">
          <input type="checkbox" id="size_52" name="size[52]"/><span>52</span>
        </label>
        <div class="action clearfix">
           <a href="#" class="go_read_filter underline">Pokaż więcej</a>
        </div>
         <div class="action clear hidden clearfix">
           <a href="#" class="btn clear">Wyczyść</a> 
         </div>
        
      </div>
      
    </div>
     <!-- start section FILTER BRAND-->
    <div class="section clearfix">
      <h3 class="open">Marka <i class="fa fa-chevron-up"></i></h3>
      <div class="content"  id="filter_manufacturer">
       <div class="form-group clearfix ">
         <input type="text" name="filter_manufacturer" id="filter_manufacturer_search" />
         <input type="image" src="/skin/frontend/modago/default/images/btn-search-filter.png" alt="Szukaj po markach">
       </div>
       <div class="wrapper-filter_manufacturerScrollbar">
         <div class="manufacturerListChecked"></div>
         <div class="filter_manufacturerScrollbar">
           <ul class="manufacturerList">

             <li class="first">
               <div class="form-group form-checkbox">
                   <input type="checkbox" name="chek_romantyczny" id="manufacturer_Adidas" class="css-checkbox">
                   <label for="manufacturer_Adidas" class="css-label"><span>Adidas</span>&nbsp;<span class="suffix">(200)</span></label>
               </div>
             </li>
               <li>
                  <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_Diesel" class="css-checkbox">
                      <label for="manufacturer_Diesel" class="css-label"><span>Diesel</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
               <li>
                <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_House" class="css-checkbox">
                      <label for="manufacturer_House" class="css-label"><span>House & House</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
               <li>
                <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_Levis" class="css-checkbox">
                      <label for="manufacturer_Levis" class="css-label"><span>Levis</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
               <li>
                <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_Reserved" class="css-checkbox">
                      <label for="manufacturer_Reserved" class="css-label"><span>Reserved</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
               <li>
                <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_On_Ona" class="css-checkbox">
                      <label for="manufacturer_On_Ona" class="css-label"><span>On i Ona</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
               <li>
                <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_Test_01" class="css-checkbox">
                      <label for="manufacturer_Test_01" class="css-label"><span>Test_01</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
               <li>
                  <div class="form-group form-checkbox">
                      <input type="checkbox" name="chek_romantyczny" id="manufacturer_Test_02" class="css-checkbox">
                      <label for="manufacturer_Test_02" class="css-label"><span>Test_02</span>&nbsp;<span class="suffix">(200)</span></label>
                  </div>
               </li>
           </ul>
         </div>
       </div>
       <div class="action clear clearfix hidden">
       <a href="#" class="btn clear">Wyczyść</a>
       </div>
      </div>
      
    </div>
    <!-- start section FILTER PRICE -->
    <div class="section clearfix">
      <h3 class="open">Cena <i class="fa fa-chevron-up"></i></h3>
      <div class="content"  id="filter_price">
         <div class="form-group form-checkbox">
           <input type="checkbox" name="filter_price_less_than_100" id="filter_price_less_than_100" class="css-checkbox"/>
           <label for="filter_price_less_than_100" class="css-label"><span>poniżej 100 zł</span> <span class="suffix">(200)</span></label>
         </div>
         <div class="form-group form-checkbox">
           <input type="checkbox" name="filter_price_100_200" id="filter_price_100_200" class="css-checkbox"/>
           <label for="filter_price_100_200" class="css-label"><span>100 do 200 zł</span> <span class="suffix">(200)</span></label>
         </div>
         <div class="form-group form-checkbox">
           <input type="checkbox" name="filter_price_200_500" id="filter_price_200_500" class="css-checkbox"/>
           <label for="filter_price_200_500" class="css-label"><span>od 2000 do 5000 zł</span> <span class="suffix">(200)</span></label>
         </div>
          <div class="form-group form-checkbox">
           <input type="checkbox" name="filter_price_above_500" id="filter_price_above_500" class="css-checkbox"/>
           <label for="filter_price_above_500" class="css-label"><span>powyżej 5000 zł</span> <span class="suffix">(200)</span></label>
         </div>
         <div id="od_do" class="clearfix">
            <div class="clearfix">
            <div id="checkSlider" class="form-group form-checkbox">
               <input type="checkbox" name="filter_slider" id="filter_slider" class="css-checkbox"/>
               <label for="filter_slider" class="css-label"></label>
            </div>
             <div class="form-horizontal">
               <div class="form-group">
                 <input type="text" id="zakres_min" /><label for="zakres_od">-</label>
               </div>
             </div>
             <div class=" form-horizontal">
               <div class="form-group">
                 <input id="zakres_max" type="text" />
               </div>
             </div>
             <div class="form-action">
               <div class="form-group">
                 <input type="submit" class="btn black pull-right" value="OK"/>
               </div>
             </div>
           </div>
            <div id="slider-range"></div>

         </div>
         <div class="action clear clearfix hidden">
         <a href="#" class="button button-third clear">Wyczyść</a>
         </div>
      </div>
    </div>
    <!-- start section FILTER STYLE SELECTLIST-->
    <div class="section clearfix">
      <h3 class="open">Styl <i class="fa fa-chevron-up"></i></h3>
      <div id="filter_style" class="content select" >

        
        <div class="select-group medium">
           <a class="button-select ajax" href="#"><span>Sortuj według:</span> <span class="down"></span></a>
           <div class="dropdown-select mCustomScrollbar">
             <ul>
               <li><a href="#" data-value="Item I"><span>Item I</span> <span class="suffix">(200)</span></a></li>
               <li><a href="#" data-value="Item II"><span>Item II</span> <span class="suffix">(200)</span></a></li>
               <li><a href="#" data-value="Item III"><span>Item III</span> <span class="suffix">(200)</span></a></li>
               <li><a href="#" data-value="Item IV"><span>Item IV</span> <span class="suffix">(200)</span></a></li>
               <li><a href="#" data-value="Item V"><span>Item V</span> <span class="suffix">(200)</span></a></li>
               <li class="divider"></li>
               <li><a href="#">Other</a></li>
             </ul>
           </div>
         </div>
        <div class="action clear clearfix hidden">
         <a href="#" class="button button-third clear">Wyczyść</a>
         </div>
      </div>
    </div>
    <!-- end :// section -->
    <!-- start section FILTER STYLE CHECKBOX-->
    <div class="section clearfix">
      <h3 class="open">Styl <i class="fa fa-chevron-up"></i></h3>
      <div  id="filter_style" class="content checkbox">
        <div class="form-group form-checkbox">
            <input type="checkbox" name="chek_romantyczny" id="chek_romantyczny" class="css-checkbox">
            <label for="chek_romantyczny" class="css-label"><span>Długa nazwa dla tego stylu</span>&nbsp;<span class="suffix">(200)</span></label>
        </div>
        <div class="form-group form-checkbox">
            <input type="checkbox" name="chek_retro" id="chek_retro" class="css-checkbox">
            <label for="chek_retro" class="css-label"><span>Retro</span> <span class="suffix">(200)</span></label>
        </div>
        <div class="action clearfix">
           <a href="#" class="go_read_filter underline">Pokaż więcej</a>
        </div>
        <div class="action clear clearfix hidden">
           <a href="#" class="button button-third clear">Wyczyść</a> 
        </div>
      </div>
    </div>
    <!-- end :// section -->
    <div class="section clearfix">
      <h3 class="open">Opinie Klientów <i class="fa fa-chevron-up"></i></h3>
      <div id="note_client" class="content">
        <div class="form-group form-checkbox">
            <input type="checkbox" name="note_client_5" id="note_client_5" class="css-checkbox">
            <label for="note_client_5" class="css-label simple"><img src="/skin/frontend/modago/default/images/raty/opinie-klientow_01.png" alt=""><span class="suffix">(230)</span></label>
        </div>
         <div class="form-group form-checkbox">
            <input type="checkbox" name="note_client_4" id="note_client_4" class="css-checkbox">
            <label for="note_client_4" class="css-label simple"><img src="/skin/frontend/modago/default/images/raty/opinie-klientow_02.png" alt="">i wyżej <span class="suffix">(23)</span></label>
        </div>
         <div class="form-group form-checkbox">
            <input type="checkbox" name="note_client_3" id="note_client_3" class="css-checkbox">
            <label for="note_client_3" class="css-label simple"><img src="/skin/frontend/modago/default/images/raty/opinie-klientow_03.png" alt="">i wyżej <span class="suffix">(230)</span></label>
        </div>
        <div class="action clear clearfix hidden">
           <a href="#" class="button button-third clear">Wyczyść</a> 
        </div>
  
      </div>
    </div>
    <!-- start section FILTER LENGTH -->
    <div class="section clearfix">
      <h3 class="open">Rodzaj <i class="fa fa-chevron-up"></i></h3>
      <div class="content"  id="filter_type">
         <div class="form-group form-checkbox">
            <table >
              <tr>
                <td class="first-td">
                  <div>
                    <input type="checkbox" name="filter_type_01" id="filter_type_01">
                    <label for="filter_type_01"><img src="/skin/frontend/modago/default/images/img-filter-type.png" alt=""></label>
                  </div>
                </td>
                <td class="last-td"><label for="filter_type_01"><span>Staniki z fiszbinkami</span> <span class="suffix">(200)</span></label></td>
              </tr>
              <tr>
                <td class="first-td">
                  <div>
                    <input type="checkbox" name="filter_type_02" id="filter_type_02">
                    <label for="filter_type_02"><img src="/skin/frontend/modago/default/images/img-filter-type.png" alt=""></label>
                  </div>
                </td>
                <td class="last-td"><label for="filter_type_02"><span>Podwiazki</span> <span class="suffix">(200)</span></label></td>
              </tr>
              <tr>
                <td class="first-td">
                  <div>
                    <input type="checkbox" name="filter_type_03" id="filter_type_03">
                    <label for="filter_type_03"><img src="/skin/frontend/modago/default/images/img-filter-type.png" alt=""></label>
                  </div>
                </td>
                <td class="last-td"><label for="filter_type_03"><span>Rajstopy modelujace</span> <span class="suffix">(200)</span></label></td>
              </tr>
            </table>
           
            
        </div>
        <div class="action clear clearfix hidden">
           <a href="#" class="button button-third clear">Wyczyść</a> 
        </div>
      </div>
    </div>
    <!-- end :// section -->
    <!-- start section Filter COLOR SLEEVE-->
    <div class="section clearfix">
      <h3 class="open">Polecane <i class="fa fa-chevron-up"></i></h3>
      <div class="content"  id="filter_recommended_products">
         <div class="form-group form-checkbox">
            <input type="checkbox" name="filter_recommended_products_news" id="filter_recommended_products_news" class="css-checkbox">
            <label for="filter_recommended_products_news" class="css-label"><span>Nowość</span> <span class="suffix">(200)</span></label>
        </div>
        <div class="form-group form-checkbox">
            <input type="checkbox" name="filter_recommended_products_sale" id="filter_recommended_products_sale" class="css-checkbox">
            <label for="filter_recommended_products_sale" class="css-label"><span>Wyprzedaż</span> <span class="suffix">(200)</span></label>
        </div>
        <div class="form-group form-checkbox">
            <input type="checkbox" name="filter_recommended_products_bestseler" id="filter_recommended_products_bestseler" class="css-checkbox">
            <label for="filter_recommended_products_bestseler" class="css-label"><span>Bestseler</span> <span class="suffix">(200)</span></label>
        </div>
        <div class="form-group form-checkbox">
            <input type="checkbox" name="filter_recommended_products_propotion" id="filter_recommended_products_propotion" class="css-checkbox">
            <label for="filter_recommended_products_propotion" class="css-label"><span>Promocja</span> <span class="suffix">(200)</span></label>
        </div>
        <div class="action clear clearfix hidden">
           <a href="#" class="button button-third clear">Wyczyść</a> 
        </div>
      </div>

    </div>
    <!-- end :// section -->
     <!-- start section Filter COLOR-->
    <div class="section clearfix">
      <h3 class="open">Kolor <i class="fa fa-chevron-up"></i></h3>
      <div class="content"  id="filter_color">
         <label for="color_01" data-img="/skin/frontend/modago/default/images/filter_color/1.png">
           <input type="checkbox" id="color_01" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_02" data-color="#6f3e18" data-img="/skin/frontend/modago/default/images/filter_color/2.png">
           <input type="checkbox" id="color_02" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_03" data-img="/skin/frontend/modago/default/images/filter_color/1.png" data-imghover="/skin/frontend/modago/default/images/filter_color/2.png">
           <input type="checkbox" id="color_03" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_04" data-color="#838383">
           <input type="checkbox" id="color_04" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_05" data-color="#ffffff">
           <input type="checkbox" id="color_05" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_06" data-color="#345aff">
           <input type="checkbox" id="color_06" name="color[]"/><span><span></span></span>
         </label>
          <label for="color_07" data-color="#22a2a4">
           <input type="checkbox" id="color_07" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_08" data-color="#657400">
           <input type="checkbox" id="color_08" name="color[]"/><span><span></span></span>
         </label>
         <label for="color_09" data-color="#fffc00">
           <input type="checkbox" id="color_09" name="color[]"/><span><span></span></span>
         </label>
         <div class="actions text-right clearfix">
            <a href="#" class="go_read_filter underline">Pokaż więcej</a>
         </div>
       <div class="action clear clearfix hidden">
           <a href="#" class="button button-third clear">Wyczyść</a> 
        </div>
        
      </div>
    </div>
    <!-- end :// section -->
    </form>
  </div>

</div>
<!-- END:/ SIDEBAR -->