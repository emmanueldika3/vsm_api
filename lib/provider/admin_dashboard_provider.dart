import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../models/admin_dashboard_model.dart';

class AdminDashboardProvider with ChangeNotifier {
  AdminDashboardData? _data;
  bool _isLoading = false;
  String? _error;

  AdminDashboardData? get data => _data;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // URL dynamique (Web Chrome / Émulateur)
  final String baseUrl = kIsWeb
      ? 'http://127.0.0.1:8000/api'
      : 'http://10.0.2.2:8000/api';

  // Charger les données du Dashboard
  Future<void> fetchDashboardData(String token) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    print("=== TENTATIVE DE CONNEXION API ===");
    print("URL: $baseUrl/dashboard/admin");

    try {
      final response = await http.get(
        Uri.parse(
          '$baseUrl/dashboard/admin',
        ), // Route exacte alignée avec Laravel
        headers: {
          'Authorization': 'Bearer $token',
          'Accept':
              'application/json', // Force Laravel à répondre en JSON (évite l'erreur Route [login])
          'Content-Type': 'application/json',
        },
      );

      print("CODE DE REPONSE: ${response.statusCode}");
      print("CORPS DE REPONSE: ${response.body}");

      if (response.statusCode == 200) {
        final Map<String, dynamic> jsonResponse = json.decode(response.body);
        final rawData = jsonResponse.containsKey('data')
            ? jsonResponse['data']
            : jsonResponse;

        _data = AdminDashboardData.fromJson(rawData);
        _error = null;
      } else if (response.statusCode == 401) {
        _error = 'Session expirée. Token invalide.';
        _loadMockDataFallback();
      } else {
        _error = 'Erreur HTTP ${response.statusCode}';
        _loadMockDataFallback();
      }
    } catch (e) {
      print("ERREUR RESEAU / PARSING: $e");
      _error = 'Impossible de joindre le serveur ($e)';
      _loadMockDataFallback();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Action : Valider un membre
  Future<bool> approveMember(int memberId, String token) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/dashboard/admin/members/$memberId/approve'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        await fetchDashboardData(token);
        return true;
      }
    } catch (e) {
      print("Erreur approveMember: $e");
    }
    return false;
  }

  void _loadMockDataFallback() {
    print("⚠️ Chargement des données Mock de secours...");
    _data = AdminDashboardData(
      activeMembers: 24,
      pendingMembersCount: 2,
      clubBalance: 350000.0,
      contributionRate: 78.5,
      pendingMembers: [
        PendingMember(
          id: 1,
          name: 'Simon Pierre',
          position: 'Milieu offensif',
          createdAt: '2026-08-20',
        ),
        PendingMember(
          id: 2,
          name: 'Emmanuel Dika',
          position: 'Défenseur',
          createdAt: '2026-08-21',
        ),
      ],
      financialSummary: FinancialSummary(
        paidCount: 18,
        totalCount: 24,
        totalIncome: 450000.0,
        totalExpenses: 100000.0,
      ),
    );
  }
}
