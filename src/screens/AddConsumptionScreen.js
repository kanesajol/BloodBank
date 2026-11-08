import { useEffect, useState } from 'react';
import { ActivityIndicator, Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import {
  createConsumptionRecord,
  fetchConsumptionMeta,
  fetchReturningConsumptionPatients,
  updateConsumptionRecord,
} from '../api/client';
import { capitalizeWords } from '../collectionForm';

export default function AddConsumptionScreen({ navigation, route }) {
  const record = route.params?.record;
  const editing = Boolean(record?.id);
  const [patientMode, setPatientMode] = useState('new');
  const [returningSearch, setReturningSearch] = useState('');
  const [returningPatients, setReturningPatients] = useState([]);
  const [returningLoading, setReturningLoading] = useState(false);
  const [selectedReturningPatient, setSelectedReturningPatient] = useState('');
  const [meta, setMeta] = useState(null);
  const [patientName, setPatientName] = useState(record?.patient_name ?? '');
  const [lastName, setLastName] = useState('');
  const [firstName, setFirstName] = useState('');
  const [middleName, setMiddleName] = useState('');
  const [suffix, setSuffix] = useState('None');
  const [age, setAge] = useState(record?.age == null ? '' : String(record.age));
  const [sex, setSex] = useState(record?.sex ?? 'M');
  const [component, setComponent] = useState(record?.blood_component ?? '');
  const [units, setUnits] = useState(record?.units == null ? '1' : String(record.units));
  const [province, setProvince] = useState(record?.province ?? '');
  const [municipality, setMunicipality] = useState(record?.municipality ?? '');
  const [barangay, setBarangay] = useState(record?.barangay ?? '');
  const [date, setDate] = useState(record?.date_submitted ?? new Date().toISOString().slice(0, 10));
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchConsumptionMeta()
      .then((data) => {
        setMeta(data);
        if (!record?.blood_component) {
          setComponent(data.components?.[0] ?? '');
        }
      })
      .catch((e) => setMessage(e.message));
  }, []);

  useEffect(() => {
    const query = returningSearch.trim();
    if (patientMode !== 'returning' || query.length < 1 || query === selectedReturningPatient) {
      setReturningPatients([]);
      setReturningLoading(false);
      return undefined;
    }

    let active = true;
    setReturningPatients([]);
    setReturningLoading(true);
    const timeout = setTimeout(() => {
      fetchReturningConsumptionPatients(query)
        .then((data) => {
          if (active) {
            setReturningPatients(data.patients ?? []);
          }
        })
        .catch((requestError) => {
          if (active) {
            setReturningPatients([]);
            setMessage(requestError.message);
          }
        })
        .finally(() => {
          if (active) {
            setReturningLoading(false);
          }
        });
    }, 250);

    return () => {
      active = false;
      clearTimeout(timeout);
    };
  }, [patientMode, returningSearch, selectedReturningPatient]);

  const municipalities = meta?.municipalitiesByProvince?.[province] ?? [];
  const barangays = meta?.places?.[municipality] ?? [];
  const barangayPlaceholder = !municipality
    ? 'Select municipality first'
    : barangays.length > 0 ? 'Select barangay' : 'No barangays available';

  const saveRecord = async () => {
    if (!editing && patientMode === 'returning' && !selectedReturningPatient) {
      setMessage('Search for and select an existing patient.');
      return;
    }
    if (patientMode !== 'returning' && (editing ? !patientName.trim() : !lastName.trim() || !firstName.trim())) {
      setMessage('Please enter the patient surname and first name.');
      return;
    }

    setSaving(true);
    setMessage('');
    try {
      const nameParts = [firstName.trim(), middleName.trim(), suffix === 'None' ? '' : suffix];
      const composedName = `${lastName.trim()}, ${nameParts.filter(Boolean).join(' ')}`;

      const payload = patientMode === 'returning' && !editing ? {
        patient_mode: 'returning',
        returning_patient_name: selectedReturningPatient,
        blood_component: component,
        units: parseInt(units, 10),
      } : {
        patient_mode: 'new',
        date_submitted: date,
        patient_name: editing ? patientName.trim() : composedName,
        age: parseInt(age, 10),
        sex,
        blood_component: component,
        units: parseInt(units, 10),
        province,
        municipality,
        barangay: barangay || undefined,
      };
      if (editing) {
        await updateConsumptionRecord(record.id, payload);
      } else {
        await createConsumptionRecord(payload);
      }
      navigation.goBack();
    } catch (requestError) {
      setMessage(requestError.message);
    } finally {
      setSaving(false);
    }
  };

  return (
    <ScrollView style={styles.root} contentContainerStyle={styles.content}>
      <View style={styles.hero}>
        <View style={styles.heroIcon}>
          <Text style={styles.heroIconText}>+</Text>
        </View>
        <View style={styles.heroCopy}>
          <Text style={styles.heroEyebrow}>CONSUMPTION</Text>
          <Text style={styles.heroTitle}>{editing ? 'Edit record' : 'Add a record'}</Text>
          <Text style={styles.heroSubtitle}>Enter patient and blood issue details.</Text>
        </View>
      </View>

      <View style={styles.sectionCard}>
        <SectionHeading number="01" title="Patient details" />
        {!editing ? (
          <View style={styles.modeToggle}>
            {[
              { value: 'new', label: 'New patient' },
              { value: 'returning', label: 'Returning patient' },
            ].map((option) => {
              const selected = patientMode === option.value;
              return (
                <Pressable
                  key={option.value}
                  accessibilityRole="button"
                  accessibilityState={{ selected }}
                  onPress={() => {
                    setPatientMode(option.value);
                    setMessage('');
                  }}
                  style={[styles.modeButton, selected && styles.modeButtonSelected]}
                >
                  <Text style={[styles.modeButtonText, selected && styles.modeButtonTextSelected]}>
                    {option.label}
                  </Text>
                </Pressable>
              );
            })}
          </View>
        ) : null}
        {!editing && patientMode === 'returning' ? (
          <>
            <FormField label="Find existing patient">
              <TextInput
                style={styles.input}
                value={returningSearch}
                onChangeText={(value) => {
                  setReturningSearch(capitalizeWords(value));
                  setSelectedReturningPatient('');
                  setMessage('');
                }}
                placeholder="Type a patient name"
                autoCapitalize="words"
              />
            </FormField>
            {returningSearch.trim().length > 0 && !selectedReturningPatient ? (
              <View style={styles.patientSuggestionPanel}>
                <View style={styles.patientSuggestionHeader}>
                  <Text style={styles.patientSuggestionHeading}>
                    {returningLoading
                      ? 'Searching patients…'
                      : `${returningPatients.length} matching patient${returningPatients.length === 1 ? '' : 's'}`}
                  </Text>
                  {returningLoading ? <ActivityIndicator size="small" color="#990000" /> : null}
                </View>
                {returningPatients.map((patientName) => (
                  <Pressable
                    key={patientName}
                    accessibilityRole="button"
                    onPress={() => {
                      setSelectedReturningPatient(patientName);
                      setReturningSearch(patientName);
                      setReturningPatients([]);
                      setMessage('');
                    }}
                    style={styles.patientSuggestion}
                  >
                    <Text style={styles.patientSuggestionName}>{patientName}</Text>
                    <Text style={styles.patientSuggestionAction}>Select</Text>
                  </Pressable>
                ))}
                {!returningLoading && returningPatients.length === 0 ? (
                  <Text style={styles.patientSuggestionEmpty}>No matching returning patient found.</Text>
                ) : null}
              </View>
            ) : null}
            {selectedReturningPatient ? (
              <Text style={styles.returningNote}>
                Selected: {selectedReturningPatient}. Existing age, sex and location will be reused; date is today.
              </Text>
            ) : null}
          </>
        ) : null}
        {patientMode === 'new' || editing ? (
          <>
        <FormField label="Date submitted">
          <TextInput
            style={styles.input}
            value={date}
            onChangeText={setDate}
            placeholder="YYYY-MM-DD"
          />
        </FormField>
        {editing ? (
          <FormField label="Patient name">
            <TextInput
              style={styles.input}
              value={patientName}
              onChangeText={(value) => setPatientName(capitalizeWords(value))}
              placeholder="Patient name"
              autoCapitalize="words"
            />
          </FormField>
        ) : (
          <View style={styles.fieldRow}>
          <FormField label="Surname" style={styles.halfField}>
            <TextInput
              style={styles.input}
              value={lastName}
              onChangeText={(value) => setLastName(capitalizeWords(value))}
              placeholder="Surname"
              autoCapitalize="words"
              returnKeyType="next"
            />
          </FormField>
          <FormField label="First name" style={styles.halfField}>
            <TextInput
              style={styles.input}
              value={firstName}
              onChangeText={(value) => setFirstName(capitalizeWords(value))}
              placeholder="First name"
              autoCapitalize="words"
              returnKeyType="next"
            />
          </FormField>
          </View>
        )}
        {!editing ? (
          <View style={styles.fieldRow}>
            <FormField label="Middle name" style={styles.halfField}>
              <TextInput
                style={styles.input}
                value={middleName}
                onChangeText={(value) => setMiddleName(capitalizeWords(value))}
                placeholder="Middle name"
                autoCapitalize="words"
              />
            </FormField>
            <FormField label="Suffix / title" style={styles.halfField}>
              <SelectField
                value={suffix}
                options={['None', 'Jr.', 'Sr.', 'II', 'III', 'IV']}
                onChange={setSuffix}
                placeholder="Select suffix"
              />
            </FormField>
          </View>
        ) : null}
        <View style={styles.fieldRow}>
          <FormField label="Age" style={styles.halfField}>
            <TextInput
              style={styles.input}
              value={age}
              onChangeText={setAge}
              keyboardType="number-pad"
              placeholder="Age"
            />
          </FormField>
          <FormField label="Sex" style={styles.halfField}>
            <SelectField value={sex} options={['M', 'F']} onChange={setSex} placeholder="Select sex" />
          </FormField>
        </View>
          </>
        ) : null}
      </View>

      <View style={styles.sectionCard}>
        <SectionHeading number="02" title="Consumption details" />
        <FormField label="Blood component">
          <SelectField
            value={component}
            options={meta?.components ?? []}
            onChange={setComponent}
            placeholder="Select component"
          />
        </FormField>
        <FormField label="No. of units crossmatched / given">
          <TextInput
            style={styles.input}
            value={units}
            onChangeText={setUnits}
            keyboardType="number-pad"
            placeholder="Enter units"
          />
        </FormField>
      </View>

      {patientMode === 'new' || editing ? (
      <View style={styles.sectionCard}>
        <SectionHeading number="03" title="Patient location" />
        <FormField label="Province">
          <SelectField
            value={province}
            options={meta?.provinces ?? []}
            onChange={(value) => {
              setProvince(value);
              setMunicipality('');
              setBarangay('');
            }}
            placeholder="Select province"
          />
        </FormField>
        <FormField label="Municipality">
          <SelectField
            value={municipality}
            options={municipalities}
            onChange={(value) => {
              setMunicipality(value);
              setBarangay('');
            }}
            placeholder={province ? 'Select municipality' : 'Select province first'}
            disabled={!province}
          />
        </FormField>
        <FormField label="Barangay">
          <SelectField
            value={barangay}
            options={barangays}
            onChange={setBarangay}
            placeholder={barangayPlaceholder}
            disabled={!municipality || barangays.length === 0}
          />
        </FormField>
      </View>
      ) : null}

      {message ? <Text style={styles.message}>{message}</Text> : null}
      <Pressable
        style={[styles.button, saving && styles.buttonDisabled]}
        onPress={saveRecord}
        disabled={saving}
        accessibilityRole="button"
      >
        <Text style={styles.buttonText}>{saving ? 'Saving record…' : editing ? 'Save changes' : 'Save record'}</Text>
      </Pressable>
    </ScrollView>
  );
}

function SectionHeading({ number, title }) {
  return (
    <View style={styles.sectionHeading}>
      <Text style={styles.sectionNumber}>{number}</Text>
      <Text style={styles.sectionTitle}>{title}</Text>
    </View>
  );
}

function FormField({ label, style, children }) {
  return (
    <View style={[styles.field, style]}>
      <Text style={styles.label}>{label}</Text>
      {children}
    </View>
  );
}

function SelectField({ value, options, onChange, placeholder, disabled = false }) {
  const [visible, setVisible] = useState(false);
  const isDisabled = disabled || options.length === 0;

  return (
    <>
      <Pressable
        style={[styles.dropdown, isDisabled && styles.dropdownDisabled]}
        onPress={() => setVisible(true)}
        disabled={isDisabled}
        accessibilityRole="button"
        accessibilityLabel={value || placeholder}
        accessibilityState={{ expanded: visible, disabled: isDisabled }}
      >
        <Text style={value ? styles.dropdownValue : styles.dropdownPlaceholder}>
          {value || placeholder}
        </Text>
        <Text style={styles.dropdownArrow}>⌄</Text>
      </Pressable>
      <Modal
        visible={visible}
        transparent
        animationType="fade"
        onRequestClose={() => setVisible(false)}
      >
        <Pressable style={styles.modalBackdrop} onPress={() => setVisible(false)}>
          <Pressable style={styles.componentPicker} onPress={(event) => event.stopPropagation()}>
            <Text style={styles.componentPickerTitle}>{placeholder}</Text>
            <ScrollView style={styles.componentOptions}>
              {options.map((option) => (
                <Pressable
                  key={option}
                  style={[styles.componentOption, option === value && styles.componentOptionSelected]}
                  onPress={() => {
                    onChange(option);
                    setVisible(false);
                  }}
                  accessibilityRole="button"
                  accessibilityState={{ selected: option === value }}
                >
                  <Text style={[styles.componentOptionText, option === value && styles.componentOptionTextSelected]}>
                    {option}
                  </Text>
                  {option === value ? <Text style={styles.componentOptionCheck}>✓</Text> : null}
                </Pressable>
              ))}
            </ScrollView>
            <Pressable
              style={styles.componentPickerCancel}
              onPress={() => setVisible(false)}
              accessibilityRole="button"
            >
              <Text style={styles.componentPickerCancelText}>Cancel</Text>
            </Pressable>
          </Pressable>
        </Pressable>
      </Modal>
    </>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: '#f8fafc' },
  content: { padding: 16, paddingBottom: 30, gap: 13 },
  hero: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 13,
    padding: 16,
    borderWidth: 1,
    borderColor: '#f3d5d8',
    borderRadius: 16,
    backgroundColor: '#fff7f7',
  },
  heroIcon: {
    width: 44,
    height: 44,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 13,
    backgroundColor: '#990000',
  },
  heroIconText: { color: '#fff', fontSize: 28, lineHeight: 32, fontWeight: '500' },
  heroCopy: { flex: 1 },
  heroEyebrow: { color: '#990000', fontSize: 10, letterSpacing: 1.1, fontWeight: '800' },
  heroTitle: { color: '#1e293b', fontSize: 20, fontWeight: '800', marginTop: 2 },
  heroSubtitle: { color: '#64748b', fontSize: 12, marginTop: 3 },
  sectionCard: {
    padding: 15,
    gap: 11,
    borderWidth: 1,
    borderColor: '#e8edf3',
    borderRadius: 15,
    backgroundColor: '#fff',
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.035,
    shadowRadius: 5,
    elevation: 1,
  },
  sectionHeading: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 9,
    paddingBottom: 9,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
    marginBottom: 1,
  },
  sectionNumber: { color: '#990000', fontSize: 10, fontWeight: '800' },
  sectionTitle: { color: '#1e293b', fontSize: 15, fontWeight: '800' },
  modeToggle: {
    flexDirection: 'row',
    gap: 8,
    padding: 5,
    borderWidth: 1,
    borderColor: '#e8dede',
    borderRadius: 10,
    backgroundColor: '#faf7f7',
  },
  modeButton: {
    flex: 1,
    minHeight: 42,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 8,
    backgroundColor: 'transparent',
  },
  modeButtonSelected: { backgroundColor: '#fff', borderWidth: 1, borderColor: '#e7b4b4' },
  modeButtonText: { color: '#64748b', fontSize: 12, fontWeight: '700' },
  modeButtonTextSelected: { color: '#990000' },
  patientSuggestion: {
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 11,
    borderWidth: 1,
    borderColor: '#e8edf3',
    borderRadius: 9,
    backgroundColor: '#fff',
  },
  patientSuggestionPanel: {
    gap: 7,
    padding: 8,
    borderWidth: 1,
    borderColor: '#e8edf3',
    borderRadius: 11,
    backgroundColor: '#f8fafc',
  },
  patientSuggestionHeader: {
    minHeight: 28,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 3,
  },
  patientSuggestionHeading: { color: '#64748b', fontSize: 11, fontWeight: '700' },
  patientSuggestionEmpty: { color: '#64748b', fontSize: 12, paddingHorizontal: 3, paddingVertical: 6 },
  patientSuggestionName: { flex: 1, color: '#1e293b', fontSize: 13, fontWeight: '700' },
  patientSuggestionAction: { color: '#990000', fontSize: 12, fontWeight: '800' },
  returningNote: { color: '#64748b', fontSize: 11, lineHeight: 16 },
  fieldRow: { flexDirection: 'row', gap: 10 },
  field: { gap: 5 },
  halfField: { flex: 1, minWidth: 0 },
  label: { color: '#475569', fontSize: 12, fontWeight: '700' },
  input: {
    minHeight: 46,
    borderWidth: 1,
    borderColor: '#dbe3ec',
    borderRadius: 10,
    paddingHorizontal: 11,
    paddingVertical: 10,
    color: '#1e293b',
    backgroundColor: '#fff',
    fontSize: 14,
  },
  dropdown: {
    minHeight: 46,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 1,
    borderColor: '#dbe3ec',
    borderRadius: 10,
    paddingHorizontal: 12,
    backgroundColor: '#fff',
  },
  dropdownDisabled: { backgroundColor: '#f8fafc' },
  dropdownValue: { color: '#1e293b', fontSize: 14 },
  dropdownPlaceholder: { color: '#94a3b8', fontSize: 14 },
  dropdownArrow: { color: '#64748b', fontSize: 18, lineHeight: 20 },
  modalBackdrop: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    backgroundColor: 'rgba(15, 23, 42, 0.48)',
  },
  componentPicker: {
    width: '100%',
    maxWidth: 380,
    maxHeight: '75%',
    borderRadius: 16,
    padding: 16,
    backgroundColor: '#fff',
  },
  componentPickerTitle: { color: '#1e293b', fontSize: 16, fontWeight: '800', marginBottom: 8 },
  componentOptions: { flexGrow: 0 },
  componentOption: {
    minHeight: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  componentOptionSelected: { backgroundColor: '#fff5f5' },
  componentOptionText: { color: '#334155', fontSize: 14 },
  componentOptionTextSelected: { color: '#990000', fontWeight: '700' },
  componentOptionCheck: { color: '#990000', fontWeight: '800' },
  componentPickerCancel: { minHeight: 42, alignItems: 'center', justifyContent: 'center', marginTop: 4 },
  componentPickerCancelText: { color: '#64748b', fontSize: 13, fontWeight: '700' },
  button: {
    minHeight: 49,
    backgroundColor: '#990000',
    borderRadius: 12,
    paddingHorizontal: 16,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 1,
    shadowColor: '#7a0000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.16,
    shadowRadius: 5,
    elevation: 2,
  },
  buttonDisabled: { opacity: 0.65 },
  buttonText: { color: '#fff', fontWeight: '800', fontSize: 15 },
  message: {
    color: '#b91c1c',
    borderWidth: 1,
    borderColor: '#fecaca',
    borderRadius: 10,
    backgroundColor: '#fff5f5',
    padding: 11,
    fontSize: 13,
    lineHeight: 18,
  },
});
