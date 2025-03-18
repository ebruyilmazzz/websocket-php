<?php

require __DIR__ . '/vendor/autoload.php'; //projede kullanılan tüm kütüphanelerin yüklenmesini sağlar.

use Ratchet\MessageComponentInterface; //WebSocket sunucusunun gerçekleştirmesi gereken metotları tanımlar
use Ratchet\ConnectionInterface; //Web socket bağlantılarını yönetmek için

class WebSocketServer implements MessageComponentInterface { //arayüzü uygulayarak gelen bağlantıları ve mesajları yönetir
    protected $clients;

    public function __construct() {
        // Bu sınıfın amacı 
        // WebSocket sunucusu oluşturulduğunda, 
        // clients koleksiyonu burada başlatılır.
        // Yani, her yeni WebSocket sunucu başlatıldığında,
        // yeni bir SplObjectStorage(Nesneleri depolamak için) nesnesi oluşturulmasıdır.
        $this->clients = new \SplObjectStorage; // Use SplObjectStorage instead of SqlObjectStorage
    }

    public function onOpen(ConnectionInterface $conn) {
        //bu sınıfın amacı onOpen(yeni bir istemci sunucuya bağlandığında çalışır )
        $this->clients->attach($conn); //yeni bağlantıyı attach metodu ile $this->clients koleksiyonuna ekler 
        echo "Bağlantı açıldı: {$conn->resourceId}\n"; //bağlantı açıldığında bağlanan istemcinin Id'sini ekrana yazdırı.
    }

    public function onClose(ConnectionInterface $conn) {
        //bu sınıfın amacı bağlantı kapandığında yapılması gerekenleri ifade eder
        $this->clients->detach($conn); //bağlantıyı detach metodu ile koleksiyondan çıkarır
        echo "Bağlantı kapandı: {$conn->resourceId}\n"; //bağlantı kapandığında istemcinin Id'sini ekrana yazarak bağlantının sonlandığını bildirir.
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        //bu sınıfın amacı gelen mesajları tüm bağlı istemcilere göndermek 
        echo "Mesaj Alındı: $msg\n"; //gelen mesajı ekrana yazdırır
        foreach ($this->clients as $client) {
            //mesajı tüm bağlı istemcilere göndermek için bi fonksiyon başlatır
            if ($client !== $from) { //mesajı gönderen istemciyi temsil eder
                $client->send($msg); //mesajı bağlantıya gönderen komuttur. Yani, tüm bağlı istemcilere mesaj gönderilir.
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        //Herhangi bir hata olduğunda çalışır
        echo "Hata: {$e->getMessage()}\n"; //hata mesajını ekrana yzdırır
        $conn->close(); //hata oluşan bağlantıyı kapatır.
    }
}

//sunucuyu Çalıştırmak için
$server = new Ratchet\App('localhost', 8082); //WebSocket sunucusunu oluşturur.Sunucu Localhost üzerinde ve 8082 portunda çalıştırılır
$server->route('/chat', new WebSocketServer, ['*']); //WebSocket için bir route (yol) tanımlar. /chat yoluna gelen bağlantılar WebSocketServer sınıfı tarafından işlenecektir. Burada ['*'] tüm başlıklara izin verir.
$server->run(); //WebSocket sunucusunu başlatır ve çalışmasını sağlar.
