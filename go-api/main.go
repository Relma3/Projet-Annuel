package main

import (
	"bytes"
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

func getDB() (*sql.DB, error) {
	dsn := os.Getenv("DB_USER") + ":" + os.Getenv("DB_PASS") +
		"@tcp(" + os.Getenv("DB_HOST") + ":3306)/" + os.Getenv("DB_NAME")
	return sql.Open("mysql", dsn)
}

func corsHeaders(w http.ResponseWriter) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Headers", "Authorization, Content-Type")
	w.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
	w.Header().Set("Content-Type", "application/json")
}

func healthHandler(w http.ResponseWriter, r *http.Request) {
	corsHeaders(w)

	json.NewEncoder(w).Encode(map[string]string{
		"status":  "ok",
		"service": "silverhappy-go-api",
		"version": "2.0.0",
	})
}

func statsHandler(w http.ResponseWriter, r *http.Request) {
	corsHeaders(w)

	db, err := getDB()
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "db impossible"})
		return
	}
	defer db.Close()

	var seniors int
	var prestataires int
	var reservations int
	var articles int
	var messages int

	db.QueryRow("SELECT COUNT(*) FROM utilisateur WHERE type_utilisateur = 'senior'").Scan(&seniors)
	db.QueryRow("SELECT COUNT(*) FROM utilisateur WHERE type_utilisateur = 'prestataire'").Scan(&prestataires)
	db.QueryRow("SELECT COUNT(*) FROM reservation WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())").Scan(&reservations)
	db.QueryRow("SELECT COUNT(*) FROM article WHERE disponible = 1").Scan(&articles)
	db.QueryRow("SELECT COUNT(*) FROM messages WHERE lu = 0").Scan(&messages)

	json.NewEncoder(w).Encode(map[string]int{
		"total_seniors":        seniors,
		"total_prestataires":   prestataires,
		"reservations_ce_mois": reservations,
		"articles_disponibles": articles,
		"messages_non_lus":     messages,
	})
}

type NotificationRequest struct {
	Message  string `json:"message"`
	IdSenior int    `json:"id_senior"`
}

func notificationsHandler(w http.ResponseWriter, r *http.Request) {
	corsHeaders(w)

	if r.Method == "OPTIONS" {
		w.WriteHeader(http.StatusNoContent)
		return
	}

	if r.Method != "POST" {
		http.Error(w, "Methode non autorisee", http.StatusMethodNotAllowed)
		return
	}

	var req NotificationRequest
	err := json.NewDecoder(r.Body).Decode(&req)
	if err != nil {
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]string{"error": "JSON invalide"})
		return
	}

	appID := os.Getenv("ONESIGNAL_APP_ID")
	apiKey := os.Getenv("ONESIGNAL_API_KEY")

	if appID == "" || apiKey == "" {
		fmt.Println("Notification :", req.Message)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": true,
			"mode":    "local",
		})
		return
	}

	payload := map[string]interface{}{
		"app_id":            appID,
		"included_segments": []string{"All"},
		"headings":          map[string]string{"fr": "Silver Happy"},
		"contents":          map[string]string{"fr": req.Message},
	}

	body, _ := json.Marshal(payload)

	httpReq, _ := http.NewRequest("POST", "https://onesignal.com/api/v1/notifications", bytes.NewBuffer(body))
	httpReq.Header.Set("Content-Type", "application/json")
	httpReq.Header.Set("Authorization", "Basic "+apiKey)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(httpReq)
	if err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
		})
		return
	}
	defer resp.Body.Close()

	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
		"status":  resp.StatusCode,
	})
}

func rappelsRdvHandler(w http.ResponseWriter, r *http.Request) {
	corsHeaders(w)

	db, err := getDB()
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "db"})
		return
	}
	defer db.Close()

	demain := time.Now().AddDate(0, 0, 1).Format("2006-01-02")

	rows, err := db.Query(`
		SELECT r.id_reservation, r.id_senior, s.prenom
		FROM reservation r
		JOIN senior s ON s.id_senior = r.id_senior
		WHERE DATE(r.date_reservation) = ? AND r.statut = 'confirme'
	`, demain)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]string{"error": "requete"})
		return
	}
	defer rows.Close()

	count := 0

	for rows.Next() {
		var idRes int
		var idSenior int
		var prenom string

		rows.Scan(&idRes, &idSenior, &prenom)

		msg := "Bonjour " + prenom + ", vous avez un RDV demain."
		fmt.Println(msg)

		count++
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"success":         true,
		"rappels_envoyes": count,
	})
}

func main() {
	port := os.Getenv("GO_PORT")
	if port == "" {
		port = "8080"
	}

	http.HandleFunc("/health", healthHandler)
	http.HandleFunc("/api/stats", statsHandler)
	http.HandleFunc("/api/notifications", notificationsHandler)
	http.HandleFunc("/api/rappels-rdv", rappelsRdvHandler)

	fmt.Println("Silver Happy Go micro-service sur le port " + port)
	http.ListenAndServe(":"+port, nil)
}
