// lib/provider/event_provider.dart

import 'package:flutter/material.dart';
import '../models/event_model.dart';
import '../services/api_service.dart';

enum EventState { initial, loading, loaded, error }

class EventProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<EventModel> _events = [];
  EventState _state = EventState.initial;
  String _errorMessage = '';

  // Getters
  List<EventModel> get events => _events;
  EventState get state => _state;
  String get errorMessage => _errorMessage;

  /// Charge la liste des événements (plus besoin du paramètre token)
  Future<void> fetchEvents({String? type}) async {
    _state = EventState.loading;
    _errorMessage = '';
    notifyListeners();

    try {
      // CORRECTION : On passe uniquement 'type', sans 'token'
      _events = await _apiService.fetchEvents(type: type);
      _state = EventState.loaded;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _state = EventState.error;
    } finally {
      notifyListeners();
    }
  }

  /// Met à jour la présence à un événement
  Future<bool> updatePresence(int eventId, String status) async {
    try {
      final success = await _apiService.updatePresence(eventId, status);
      if (success) {
        // Recharger les événements pour actualiser le statut
        await fetchEvents();
      }
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
      return false;
    }
  }
}
