# PHPDatabaseClass
Query creater class for database on PHP


İki klasörüde değiştirmeden dizine at.

'lib/DB.php' dosyasını projeye ekle.

'config' kalsöründeki database.php dosyasında veritabanı sunucu bilgileri var.

İsteğe göre değiştirilebilir.


    DB::table("tablo_ismi")			//tabloyu seçer

Query fonksiyonu direk sorgu yapılabilir fonksiyondur. Girdileri dizi ile ikinci parametre olarak verebilirsin. Örnek etiket kullanımları yazdım

Parametre dizisinde anahtar kısmına soyadi olarakta yazabilirsin :soyadi olarakta. İkisinide kabul ediyor.

Birde adi=:adi şeklinde yazdım ama :adi kısmı ilk kısımla aynı olmak zorunda degil

Fonksiyon execute işlemini yapıp direk sonuc dönderir.

    DB::table("tablo_ismi")->query("SELECT * FROM tablo_ismi WHERE adi=:adi AND soyadi=:soyadi OR telefon=:numara", ["adi" => "yusuf", ":soyadi" => "comert", "numara" => "05123456789"]);
  
3ü de aynı işi yaparlar SELECT * FROM tablo_ismi    Çalışır

    DB::table("tablo_ismi")->getAll();
    
    DB::table("tablo_ismi")->execute();	 
    
    DB::table("tablo_ismi")->exec();	

Execute işlemini kendisi yapar. Yeniden yapmaya gerek yok

    DB::table("tablo_ismi")->insert("adi", "yusuf");							
    
    DB::table("tablo_ismi")->insert(["adi" => "yusuf", "soyadi" => "comert", "telefon" => "05123456789"]);	

Id si 5 olan (execute kendisi yapar)

    DB::table("tablo_ismi")->delete("id", 5);					
    
Tum parametreleri AND ler (execute kendisi yapar)

    DB::table("tablo_ismi")->delete(["adi" => "yusuf", "soyadi" => "comert"]);	

Parametre olmazsa execute yapmaz where() fonksiyonları kullanılabilir

    DB::table("tablo_ismi")->delete()->where("id", 5);				

Update işlemi

    DB::table("tablo_ismi")->update("adi","emre")->where("id", 5)->exec();						
    
    DB::table("tablo_ismi")->update(["adi" => "emre", "soyadi" => "kayaer"])->where("adi", "yusuf")->execute();	


Select fonksiyonu custom olarak column adi vermek için

    DB::table("tablo_ismi")->select("adi")->getAll();			

Varsayılan * olduğu için select()'i hiç kullanmazsan tüm column ları çeker

    DB::table("tablo_ismi")->select(["adi", "soyadi", "telefon"])->exec();	

Tüm parametleri AND ler

    DB::table("tablo_ismi")->where("adi", "yusuf")->getAll();
    
    DB::table("tablo_ismi")->where(["adi" => "yusuf", "soyadi" => "comert"])->execute(); 	

Tüm parametreler AND lenir

    DB::table("tablo_ismi")->andWhere(["adi" => "yusuf", "soyadi" => "comert"])->exec();	

Tüm parametreler OR lanır

    DB::table("tablo_ismi")->orWhere(["adi" => "yusuf", "soyadi" => "comert"])->exec();	

Örnek kullanım

    DB::table("tablo_ismi")->where("adi", "yusuf")->orWhere("soyadi", "comert")->andWhere("email", "yusufcmrt@gmail.com")->exec();
    
    DB::table("tablo_ismi")->orWhere(["adi" => "yusuf", "soyadi" => "comert"])->andWhere(["bolum" => 5, "okul" => 3])->exec();

Herhangi sorguda limit belirler. execute() fonksiyonundan hemen önce kullanılması gerekir

    DB::table("tablo_ismi")->limit(5)->exec();	

Tarih e göre varsayılan asc olarak sıralar

    DB::table("tablo_ismi")->orderBy("tarih");				

Tarih e göre desc olarak sıralar. İkinci parametre varsayılanı belirler. yani burada varsayılan desc oldu

    DB::table("tablo_ismi")->orderBy("tarih","desc");			

Tarih desc, id asc olarak sıralar

    DB::table("tablo_ismi")->orderBy(["tarih" => "desc", "id" => "asc"]);	

Tarih asc, id asc olarak sıralar. Dizide türü belirtilmeyenler varsayılan asc ile sıralanır

    DB::table("tablo_ismi")->orderBy(["tarih", "id"]);

Tarih desc, id desc olarak sıralar. İkinci parametre de varsayılan decs yapıldığı için hepsi desc olarak sıralandı

    DB::table("tablo_ismi")->orderBy(["tarih", "id"], "desc");

Tarih asc, id desc olarak sıralar. Dizide türü belirtilmeyenler varsayılan asc ile sıralanır

    DB::table("tablo_ismi")->orderBy(["tarih", "id" => "desc"]);

Tarih asc, id desc olarak siralar. İkinci parametre de varsayılan decs yapıldığı için türü verilmeyenler desc olarak sıralandı

    DB::table("tablo_ismi")->orderBy(["tarih" => "asc", "id"], "desc");	

En son yapılan sorguyu string olarak return eder

    DB::getLastQuery();	



DatabaseResult Class
---

Tüm sonuçlar DatabaseResult class'ı tipinde döner. Burdan sonrasında bu class'ın kullanımından bahsedicem.

    $kullanici = DB::table("kullanicilar")->where(["email" => "yusufcmrt@gmail.com", "parola" => "123456"])->exec();

Sonuç döndü mü dönmedi mi kontrolü

    $kullanici->isNull(); 

Kaç satır geldiğini int olarak return eder

    $kullanici->length();	

Var ise ilk satırı aktifleştirir ve true dönderir. satır yok ise false dönderir. birden fazla satır var ise ilk satıra atlar ve next() ile devam edebilir.

    $kullanici->first();	

Aktif satırdaki adi column'undaki veriyi verir. $kullanici->comlumn_name; şeklinde kullanılır

    $kullanici->adi;	

Bir sonraki satır var ise aktifleştirir ve true sonucu dönderir. satır yok ise false dönderir.

    $kullanici->next();	

Örnek kullanım 

    $kullanici = DB::table("kullanicilar")->where(["email" => "yusufcmrt@gmail.com", "parola" => "123456"])->exec();
    
    if(!$kullanici->isNull()){
    
    	$kullanici->first();
    
    	echo $kullanici->email;
    
    }



Örnek kullanım 

    $kullanici = DB::table("kullanicilar")->where(["email" => "yusufcmrt@gmail.com", "parola" => "123456"])->exec();
    
    if($kullanici->first()){
    
    	echo $kullanici->email;
    
    }



Örnek kullanım 

    $kullanici = DB::table("kullanicilar")->where(["email" => "yusufcmrt@gmail.com", "parola" => "123456"])->exec();
    
    while($kullanici->next()){
    
    	echo $kullanici->email;
    
    	echo '<br/>';
    
    }



Örnek kullanım 

    $kullanici = DB::table("kullanicilar")->where(["email" => "yusufcmrt@gmail.com", "parola" => "123456"])->exec();
    
    //tüm satırları tek tek okuyup sona kadar döner
    
    while($kullanici->next()){	
    
    	echo $kullanici->email;
    
    	echo '<br/>';
    
    }				

Tekrar ilk satıra döndü

    $kullanici->first();		
    
    echo $kullanici->email;
    
    echo '<br/>';

İlk satıra dönülmüştü bu yüzden bir sonraki satır olan 2 satırdan başlayıp tekrar sonuna kadar dönecek

  while($kullanici->next()){	
  
  	echo $kullanici->email;
  
  	echo '<br/>';
  }



Örnek kullanım 

    $kullanici = DB::table("kullanicilar")->where(["email" => "yusufcmrt@gmail.com", "parola" => "123456"])->exec();
    
    //sürekli 1. satırı çekeceği için sonsuz döngü
    
    while($kullanici->first()){	
    
    	echo $kullanici->email;
    
    	echo '<br/>';
    
    }

