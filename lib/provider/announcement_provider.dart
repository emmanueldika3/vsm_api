import 'dart:convert';
import 'dart:developer';
import 'dart:io' show Platform;
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class Announcement {
  final String id;
  final String title;
  final String content;
  final bool isUrgent;
  final DateTime createdAt;

  Announcement({
    required this.id,
    required this.title,
    required this.content,
    this.isUrgent = false,
    required this.createdAt,
  });

  factory Announcement.fromJson(Map<String, dynamic> json) {
    bool parseBool(dynamic val) {
      if (val is bool) return val;
      if (val is int) return val == 1;
      if (val is String) return val == '1' || val.toLowerCase() == 'true';
      return false;
    }

    return Announcement(
      id: json['id'].toString(),
      title: json['title']?.toString() ?? '',
      content: json['content']?.toString() ?? '',
      isUrgent: parseBool(json['isUrgent'] ?? json['is_urgent']),
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'].toString())
          : DateTime.now(),
    );
  }
}

class AnnouncementProvider extends ChangeNotifier {
  /// URL s'adaptant à la plateforme de test (Web/Chrome, Android, iOS)
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api/announcements';
    } else if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api/announcements';
    } else {
      return 'http://127.0.0.1:8000/api/announcements';
    }
  }

  List<Announcement> _announcements = [];
  bool _isLoading = false;
  String? _token;

  List<Announcement> get announcements => List.unmodifiable(_announcements);
  bool get isLoading => _isLoading;
  String? get token => _token;

  /// Définir ou mettre à jour le jeton d'authentification
  void setToken(String? token) {
    _token = token;
    notifyListeners();
  }

  /// En-têtes HTTP incluant le Token Bearer si disponible
  Map<String, String> get _headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (_token != null && _token!.isNotEmpty) 'Authorization': 'Bearer $_token',
  };

  /// Récupérer la liste depuis la BD
  Future<void> fetchAnnouncements() async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await http.get(Uri.parse(baseUrl), headers: _headers);

      log('Fetch Status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        final List<dynamic> data = decoded is Map<String, dynamic>
            ? (decoded['data'] ?? [])
            : (decoded as List<dynamic>);

        _announcements = data
            .map((item) => Announcement.fromJson(item as Map<String, dynamic>))
            .toList();
      } else {
        throw Exception(
          'Erreur de chargement (${response.statusCode}): ${response.body}',
        );
      }
    } catch (e, stackTrace) {
      log('Erreur fetchAnnouncements: $e', stackTrace: stackTrace);
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Ajouter en BD
  Future<bool> addAnnouncement({
    required String title,
    required String content,
    bool isUrgent = false,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(baseUrl),
        headers: _headers,
        body: json.encode({
          'title': title,
          'content': content,
          'isUrgent': isUrgent,
        }),
      );

      log("STATUS CODE : ${response.statusCode}");
      log("RESPONSE BODY : ${response.body}");

      if (response.statusCode == 201 || response.statusCode == 200) {
        try {
          await fetchAnnouncements();
        } catch (e) {
          log('Erreur lors du rafraîchissement de la liste: $e');
        }
        return true;
      } else {
        throw Exception('Code HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e, stackTrace) {
      log('Erreur addAnnouncement: $e', stackTrace: stackTrace);
      rethrow;
    }
  }

  /// Modifier en BD
  Future<bool> updateAnnouncement({
    required String id,
    required String title,
    required String content,
    required bool isUrgent,
  }) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl/$id'),
        headers: _headers,
        body: json.encode({
          'title': title,
          'content': content,
          'isUrgent': isUrgent,
        }),
      );

      log('Update Status: ${response.statusCode}');
      log('Update Response: ${response.body}');

      if (response.statusCode == 200) {
        await fetchAnnouncements();
        return true;
      } else {
        throw Exception('Code HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e, stackTrace) {
      log('Erreur updateAnnouncement: $e', stackTrace: stackTrace);
      rethrow;
    }
  }

  /// Supprimer en BD
  Future<bool> deleteAnnouncement(String id) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/$id'),
        headers: _headers,
      );

      log('Delete Status: ${response.statusCode}');

      if (response.statusCode == 200 || response.statusCode == 204) {
        _announcements.removeWhere((item) => item.id == id);
        notifyListeners();
        return true;
      } else {
        throw Exception('Code HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e, stackTrace) {
      log('Erreur deleteAnnouncement: $e', stackTrace: stackTrace);
      rethrow;
    }
  }
}
