import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:developer';
import '../models/event_model.dart';
import '../models/announcement_model.dart';
import '../models/contribution_model.dart';
import '../models/user_model.dart';
import 'package:flutter/foundation.dart';

class ApiService {
  // Adresse IP de votre serveur local (Émulateur Android = 10.0.2.2)
  // Appareil physique = IP de votre PC (ex: http://192.168.1.50:8000/api)
  static const String baseUrl = 'http://127.0.0.1:8000/api';

  // Clef de stockage local du Token JWT / Sanctum
  static const String _tokenKey = 'auth_token';

  // ==================== GESTION DU TOKEN & EN-TÊTES ====================

  /// Récupère le Token stocké dans SharedPreferences
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  /// Génère dynamiquement les en-têtes HTTP avec le Token
  Future<Map<String, String>> _getHeaders({bool requiresAuth = true}) async {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (requiresAuth) {
      final token = await _getToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }

    return headers;
  }

  // ==================== MÉTHODES HTTP GÉNÉRIQUES ====================

  /// Méthode générique HTTP GET
  Future<dynamic> _get(
    String endpoint, {
    Map<String, String>? queryParams,
    bool requiresAuth = true,
  }) async {
    try {
      final uri = Uri.parse(
        '$baseUrl$endpoint',
      ).replace(queryParameters: queryParams);
      final headers = await _getHeaders(requiresAuth: requiresAuth);

      final response = await http.get(uri, headers: headers);
      return _processResponse(response);
    } on SocketException {
      throw Exception(
        'Impossible de joindre le serveur. Vérifiez votre connexion.',
      );
    } on http.ClientException {
      throw Exception('Échec de la connexion HTTP vers le serveur.');
    }
  }

  /// Méthode générique HTTP POST
  Future<dynamic> _post(
    String endpoint, {
    Map<String, dynamic>? body,
    bool requiresAuth = true,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final headers = await _getHeaders(requiresAuth: requiresAuth);

      final response = await http.post(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } on SocketException {
      throw Exception(
        'Impossible de joindre le serveur. Vérifiez votre connexion.',
      );
    } on http.ClientException {
      throw Exception('Échec de la connexion HTTP vers le serveur.');
    }
  }

  /// Méthode générique HTTP PUT
  Future<dynamic> _put(
    String endpoint, {
    Map<String, dynamic>? body,
    bool requiresAuth = true,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final headers = await _getHeaders(requiresAuth: requiresAuth);

      final response = await http.put(
        uri,
        headers: headers,
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } on SocketException {
      throw Exception(
        'Impossible de joindre le serveur. Vérifiez votre connexion.',
      );
    } on http.ClientException {
      throw Exception('Échec de la connexion HTTP vers le serveur.');
    }
  }

  /// Méthode générique HTTP DELETE
  Future<dynamic> _delete(String endpoint, {bool requiresAuth = true}) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final headers = await _getHeaders(requiresAuth: requiresAuth);

      final response = await http.delete(uri, headers: headers);
      return _processResponse(response);
    } on SocketException {
      throw Exception(
        'Impossible de joindre le serveur. Vérifiez votre connexion.',
      );
    } on http.ClientException {
      throw Exception('Échec de la connexion HTTP vers le serveur.');
    }
  }

  /// Traitement centralisé des codes de réponse HTTP et erreurs
  dynamic _processResponse(http.Response response) {
    dynamic body;

    // Protection au cas où le serveur renvoie du HTML (page d'erreur Laravel/Apache)
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      throw Exception('Réponse invalide du serveur (${response.statusCode}).');
    }

    switch (response.statusCode) {
      case 200:
      case 201:
        return body;
      case 401:
        throw Exception(
          'Session expirée ou non autorisée. Veuillez vous reconnecter.',
        );
      case 403:
        throw Exception(
          'Accès refusé. Vous n\'avez pas les permissions requises.',
        );
      case 404:
        throw Exception('Ressource introuvable.');
      case 422:
        final errors = (body is Map && body['errors'] != null)
            ? jsonEncode(body['errors'])
            : (body is Map ? body['message'] : 'Données non valides.');
        throw Exception('Données invalides : $errors');
      default:
        final msg = (body is Map && body['message'] != null)
            ? body['message']
            : 'Erreur serveur';
        throw Exception(
          'Une erreur est survenue (${response.statusCode}) : $msg',
        );
    }
  }

  // ==================== AUTHENTIFICATION ====================

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _post(
      '/login',
      body: {'email': email, 'password': password},
      requiresAuth: false,
    );

    if (response is Map && response['token'] != null) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, response['token'].toString());
    }
    return Map<String, dynamic>.from(response);
  }

  Future<void> logout() async {
    try {
      await _post('/logout');
    } catch (_) {
      // Ignore les erreurs lors de la déconnexion distante
    } finally {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_tokenKey);
    }
  }

  Future<UserModel> getProfile() async {
    final response = await _get('/me');
    return UserModel.fromJson(response['data'] ?? response);
  }

  // ==================== ÉVÉNEMENTS ====================

  Future<List<EventModel>> fetchEvents({String? type}) async {
    final queryParams = type != null ? {'type': type} : null;
    final response = await _get('/events', queryParams: queryParams);
    final List data = response is Map ? (response['data'] ?? []) : response;
    return data.map((json) => EventModel.fromJson(json)).toList();
  }

  Future<EventModel> fetchEventDetails(int id) async {
    final response = await _get('/events/$id');
    return EventModel.fromJson(response['data'] ?? response);
  }

  Future<bool> updatePresence(int eventId, String status) async {
    final response = await _post(
      '/events/$eventId/presence',
      body: {'status': status},
    );
    return response['status'] == 'success' || response['success'] == true;
  }

  // ==================== ANNONCES ====================

  Future<List<AnnouncementModel>> fetchAnnouncements() async {
    try {
      final response = await _get('/announcements');

      final List data = response is Map<String, dynamic>
          ? (response['data'] ?? [])
          : (response is List ? response : []);

      return data.map((json) => AnnouncementModel.fromJson(json)).toList();
    } catch (e, stack) {
      log('Erreur fetchAnnouncements', error: e, stackTrace: stack);
      rethrow;
    }
  }

  Future<bool> createAnnouncement({
    required String title,
    required String content,
    bool isUrgent = false, // Renommé pour correspondre à la colonne BDD
  }) async {
    try {
      final response = await _post(
        '/announcements',
        body: {
          'title': title,
          'content': content,
          'isUrgent': isUrgent, // Mapping exact avec la colonne BDD 'isUrgent'
        },
      );

      if (response is Map) {
        if (response['status'] == 'success' ||
            response['success'] == true ||
            response.containsKey('data') ||
            response.containsKey('id')) {
          return true;
        }
      }

      return true;
    } catch (e, stack) {
      log('Erreur fetchAnnouncements', error: e, stackTrace: stack);
      rethrow;
    }
  }

  // ==================== COTISATIONS / FINANCES ====================

  Future<List<ContributionModel>> fetchContributions() async {
    final response = await _get('/contributions');
    final List data = response is Map ? (response['data'] ?? []) : response;
    return data.map((json) => ContributionModel.fromJson(json)).toList();
  }
}
