package main

import (
	"crypto/tls"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"strings"
)

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

	client := &http.Client{
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	resp, err := client.Get("https://silverhappy-web-1/api/admin.php?action=stats")
	if err != nil || resp.StatusCode != 200 {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"total_seniors":      0,
			"total_prestataires": 0,
			"reservations_mois":  0,
		})
		return
	}
	defer resp.Body.Close()

	var data interface{}
	json.NewDecoder(resp.Body).Decode(&data)
	json.NewEncoder(w).Encode(data)
}

func notificationsHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req interface{}
	json.NewDecoder(r.Body).Decode(&req)

	body, _ := json.Marshal(req)
	resp, err := http.Post(
		"http://silverhappy-web-1/api/save_player_id.php",
		"application/json",
		strings.NewReader(string(body)),
	)
	if err != nil || resp.StatusCode != 200 {
		w.WriteHeader(http.StatusCreated)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": true,
			"message": "Notification transmise",
		})
		return
	}
	defer resp.Body.Close()

	var data interface{}
	json.NewDecoder(resp.Body).Decode(&data)
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(data)
}

func main() {
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
