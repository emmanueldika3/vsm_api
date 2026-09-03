import 'dart:io';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';

class AuthProvider extends ChangeNotifier {
  // Configuration de l'URL de base de l'API Laravel
  // Pour Android Emulator, utilisez 'http://10.0.2.2:8000/api'
  // Pour un téléphone physique sur le même WiFi, utilisez l'IP locale (ex: 'http://192.168.1.50:8000/api')
  // static const String baseUrl = 'http://10.0.2.2:8000/api';
  static const String baseUrl =
      'http://127.0.0.1:8000/api'; // Remplacer par votre IP réelle

  UserModel? _user;
  String? _token;
  bool _isLoading = false;
  String? _errorMessage;

  // Getters pour l'interface UI
  UserModel? get user => _user;
  String? get token => _token;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _token != null && _user != null;

  AuthProvider() {
    // Tentative d'auto-connexion au chargement du provider
    tryAutoLogin();
  }

  // 🔑 CONNEXION PAR TÉLÉPHONE & MOT DE PASSE
  Future<bool> login({required String phone, required String password}) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'phone': phone, 'password': password}),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        _token = responseData['token'];
        _user = UserModel.fromJson(responseData['user']);

        // Sauvegarde locale dans SharedPreferences
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('auth_user', jsonEncode(_user!.toJson()));

        _setLoading(false);
        notifyListeners();
        return true;
      } else {
        _errorMessage = responseData['message'] ?? 'Identifiants incorrects.';
        _setLoading(false);
        return false;
      }
    } catch (error) {
      _errorMessage = 'Erreur de connexion réseau. Vérifiez le serveur.';
      _setLoading(false);
      return false;
    }
  }

  Future<bool> updateProfilePhoto(File imageFile) async {
    try {
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/user/photo'),
      );

      request.headers.addAll({
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      });

      request.files.add(
        await http.MultipartFile.fromPath('photo', imageFile.path),
      );

      var response = await request.send();

      if (response.statusCode == 200) {
        // Recharger les données profil ou mettre à jour le state local
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint('Erreur lors de l\'envoi de la photo : $e');
    }
    return false;
  }

  // 🔄 AUTO-CONNEXION (Au démarrage de l'application)
  Future<bool> tryAutoLogin() async {
    final prefs = await SharedPreferences.getInstance();

    if (!prefs.containsKey('auth_token') || !prefs.containsKey('auth_user')) {
      return false;
    }

    try {
      _token = prefs.getString('auth_token');
      final userMap = jsonDecode(prefs.getString('auth_user')!);
      _user = UserModel.fromJson(userMap);

      notifyListeners();
      return true;
    } catch (e) {
      await logout();
      return false;
    }
  }

  // 🚪 DÉCONNEXION
  Future<void> logout() async {
    if (_token != null) {
      try {
        // Optionnel : Révoquer le token côté Laravel Sanctum
        await http.post(
          Uri.parse('$baseUrl/logout'),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer $_token',
          },
        );
      } catch (_) {
        // En cas d'erreur réseau, on poursuit la déconnexion locale
      }
    }

    _user = null;
    _token = null;

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('auth_user');

    notifyListeners();
  }

  // Helpers internes
  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
  }
}
