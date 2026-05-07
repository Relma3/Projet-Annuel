package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

var db *sql.DB

func initDB() {
	host := os.Getenv("DB_HOST")
	name := os.Getenv("DB_NAME")
	user := os.Getenv("DB_USER")
	pass := os.Getenv("DB_PASS")

	dsn := fmt.Sprintf("%s:%s@tcp(%s:3306)/%s?parseTime=true", user, pass, host, name)

	var err error
	for i := 0; i < 10; i++ {
		db, err = sql.Open("mysql", dsn)
		if err == nil {
			if err = db.Ping(); err == nil {
				fmt.Println("Connecté à MySQL")
				return
			}
		}
		fmt.Printf("Attente BDD... tentative %d/10\n", i+1)
		time.Sleep(3 * time.Second)
	}
	fmt.Println("Impossible de se connecter à MySQL:", err)
}

func healthHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"status":  "ok",
		"service": "silverhappy-go-api",
		"version": "2.0.0",
	})
}

func statsHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	var totalSeniors, totalPrestataires, reservationsMois, abonnementsActifs int

	db.QueryRow("SELECT COUNT(*) FROM senior").Scan(&totalSeniors)
	db.QueryRow("SELECT COUNT(*) FROM prestataire WHERE statut = 'valide'").Scan(&totalPrestataires)
	db.QueryRow("SELECT COUNT(*) FROM reservation WHERE MONTH(date_reservation) = MONTH(NOW()) AND YEAR(date_reservation) = YEAR(NOW())").Scan(&reservationsMois)
	db.QueryRow("SELECT COUNT(*) FROM abonnement WHERE statut = 'actif'").Scan(&abonnementsActifs)

	var ca float64
	db.QueryRow("SELECT COALESCE(SUM(montant_cents)/100, 0) FROM paiements WHERE statut = 'reussi'").Scan(&ca)

	json.NewEncoder(w).Encode(map[string]interface{}{
		"total_seniors":      totalSeniors,
		"total_prestataires": totalPrestataires,
		"reservations_mois":  reservationsMois,
		"abonnements_actifs": abonnementsActifs,
		"chiffre_affaires":   ca,
		"commissions_sh":     ca * 0.01,
	})
}

func notificationsHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req struct {
		UserID  int    `json:"user_id"`
		Message string `json:"message"`
		Type    string `json:"type"`
		Titre   string `json:"titre"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "Invalid JSON", http.StatusBadRequest)
		return
	}

	if req.Titre == "" {
		req.Titre = "Notification Silver Happy"
	}
	if req.Type == "" {
		req.Type = "info"
	}

	// Insérer en BDD
	_, err := db.Exec(
		"INSERT INTO notification (id_senior, titre, message, type) VALUES (?, ?, ?, ?)",
		req.UserID, req.Titre, req.Message, req.Type,
	)
	if err != nil {
		http.Error(w, "Erreur BDD: "+err.Error(), http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
		"message": fmt.Sprintf("Notification enregistrée pour user %d", req.UserID),
	})
}

func main() {
	initDB()

	port := os.Getenv("GO_PORT")
	if port == "" {
		port = "8080"
	}

	http.HandleFunc("/health", healthHandler)
	http.HandleFunc("/api/stats", statsHandler)
	http.HandleFunc("/api/notifications", notificationsHandler)

	fmt.Printf("Go API Silver Happy démarrée sur le port %s\n", port)
	http.ListenAndServe(":"+port, nil)
}
