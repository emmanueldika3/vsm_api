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

  List<EventModel> get events => _events;
  EventState get state => _state;
  String get errorMessage => _errorMessage;

  // 📍 AJOUTEZ CES DEUX GETTERS POUR FACILITER LA LECTURE DANS L'ÉCRAN :
  bool get isLoading => _state == EventState.loading;
  bool get hasError => _state == EventState.error;

  Future<void> fetchEvents({String? type}) async {
    _state = EventState.loading;
    _errorMessage = '';
    notifyListeners();

    try {
      _events = await _apiService.fetchEvents(type: type);
      _state = EventState.loaded;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _state = EventState.error;
    } finally {
      notifyListeners();
    }
  }
}
